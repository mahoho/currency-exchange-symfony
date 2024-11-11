<?php

namespace App\Tests\Service\PullRates;

use App\DTO\RateDTO;
use App\Entity\RateSource;
use App\Repository\ExchangeRateRepository;
use App\Service\PullRates\CbrPullRatesService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class CbrPullRatesServiceTest extends TestCase {
    private EntityManagerInterface $entityManagerMock;
    private ExchangeRateRepository $exchangeRateRepositoryMock;
    private CbrPullRatesService $pullRatesService;
    private MockHttpClient $httpClientMock;

    protected function setUp(): void {
        // Mock dependencies
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->exchangeRateRepositoryMock = $this->createMock(ExchangeRateRepository::class);

        // Create a RateSource for CBR
        $rateSource = new RateSource();
        $rateSource->setName('CBR');
        $rateSource->setBaseCurrencyCode('RUB');
        $rateSource->setUrl('http://example.com');

        // Mock the EntityManager to return the RateSource when requested
        $rateSourceRepositoryMock = $this->createMock(EntityRepository::class);
        $rateSourceRepositoryMock->method('findOneBy')
            ->with(['name' => 'CBR'])
            ->willReturn($rateSource);

        $this->entityManagerMock->method('getRepository')
            ->with(RateSource::class)
            ->willReturn($rateSourceRepositoryMock);
    }

    public function testFetchRates() {
        $xmlContent = <<<XML
<?xml version="1.0" encoding="windows-1251"?>
<ValCurs Date="02.09.2021" name="Foreign Currency Market">
    <Valute ID="R01235">
        <NumCode>840</NumCode>
        <CharCode>USD</CharCode>
        <Nominal>1</Nominal>
        <Name>US Dollar</Name>
        <Value>73,4567</Value>
    </Valute>
    <Valute ID="R01239">
        <NumCode>978</NumCode>
        <CharCode>EUR</CharCode>
        <Nominal>1</Nominal>
        <Name>Euro</Name>
        <Value>86,7890</Value>
    </Valute>
</ValCurs>
XML;

        $response = new MockResponse($xmlContent);

        // Update the MockHttpClient to return the new response
        $this->httpClientMock = new MockHttpClient($response);

        // Re-instantiate the service with the updated client
        $this->pullRatesService = new CbrPullRatesService(
            $this->entityManagerMock,
            $this->httpClientMock,
            $this->exchangeRateRepositoryMock
        );

        $rates = $this->pullRatesService->fetchRates();

        $this->assertCount(2, $rates);

        /** @var RateDTO $usdRate */
        $usdRate = $rates[0];
        $this->assertEquals('USD', $usdRate->getCurrencyCode());
        $this->assertEquals(73.4567, $usdRate->getRate());
        $this->assertEquals('RUB', $usdRate->getBaseCurrencyCode());

        /** @var RateDTO $eurRate */
        $eurRate = $rates[1];
        $this->assertEquals('EUR', $eurRate->getCurrencyCode());
        $this->assertEquals(86.7890, $eurRate->getRate());
        $this->assertEquals('RUB', $eurRate->getBaseCurrencyCode());
    }

    public function testSupports() {
        $httpClientMock = new MockHttpClient();

        // Instantiate the CbrPullRatesService
        $this->pullRatesService = new CbrPullRatesService(
            $this->entityManagerMock,
            $httpClientMock,
            $this->exchangeRateRepositoryMock
        );

        $this->assertTrue($this->pullRatesService->supports('CBR'));
        $this->assertFalse($this->pullRatesService->supports('OtherProvider'));
    }
}

