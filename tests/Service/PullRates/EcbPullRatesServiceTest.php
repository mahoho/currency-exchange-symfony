<?php

namespace App\Tests\Service\PullRates;

use App\DTO\RateDTO;
use App\Entity\RateSource;
use App\Repository\ExchangeRateRepository;
use App\Service\PullRates\EcbPullRatesService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

class EcbPullRatesServiceTest extends TestCase {
    private EntityManagerInterface $entityManagerMock;
    private ExchangeRateRepository $exchangeRateRepositoryMock;
    private EcbPullRatesService $pullRatesService;

    protected function setUp(): void {
        // Mock dependencies
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->exchangeRateRepositoryMock = $this->createMock(ExchangeRateRepository::class);

        // Create a RateSource for ECB
        $rateSource = new RateSource();
        $rateSource->setName('ECB');
        $rateSource->setBaseCurrencyCode('EUR');
        $rateSource->setUrl('http://example.com');

        // Mock the EntityManager to return the RateSource when requested
        $rateSourceRepositoryMock = $this->createMock(EntityRepository::class);
        $rateSourceRepositoryMock->method('findOneBy')
            ->willReturn($rateSource);

        $this->entityManagerMock->method('getRepository')
            ->with(RateSource::class)
            ->willReturn($rateSourceRepositoryMock);

    }

    public function testFetchRates() {
        $xmlContent = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Envelope>
    <Cube>
        <Cube time="2021-09-01">
            <Cube currency="USD" rate="1.1847"/>
            <Cube currency="JPY" rate="129.53"/>
        </Cube>
    </Cube>
</Envelope>
XML;

        // Configure the ExchangeRateRepository mock
        $this->exchangeRateRepositoryMock
            ->method('findOneBy')
            ->willReturn(null);

        $response = new MockResponse($xmlContent);
        $httpClientMock = new MockHttpClient($response);
        $this->pullRatesService = new EcbPullRatesService(
            $this->entityManagerMock,
            $httpClientMock,
            $this->exchangeRateRepositoryMock
        );

        $rates = $this->pullRatesService->fetchRates();

        $this->assertCount(2, $rates);

        /** @var RateDTO $usdRate */
        $usdRate = $rates[0];
        $this->assertEquals('USD', $usdRate->getCurrencyCode());
        $this->assertEquals(1.1847, $usdRate->getRate());
        $this->assertEquals('EUR', $usdRate->getBaseCurrencyCode());

        /** @var RateDTO $jpyRate */
        $jpyRate = $rates[1];
        $this->assertEquals('JPY', $jpyRate->getCurrencyCode());
        $this->assertEquals(129.53, $jpyRate->getRate());
        $this->assertEquals('EUR', $jpyRate->getBaseCurrencyCode());
    }

    public function testSupports() {
        $httpClientMock = new MockHttpClient();

        $this->pullRatesService = new EcbPullRatesService(
            $this->entityManagerMock,
            $httpClientMock,
            $this->exchangeRateRepositoryMock
        );

        $this->assertTrue($this->pullRatesService->supports('ECB'));
        $this->assertFalse($this->pullRatesService->supports('OtherProvider'));
    }
}
