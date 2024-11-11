<?php

namespace App\Tests\Service;

use App\DataFixtures\ExchangeRateFixture;
use App\Entity\ExchangeRate;
use App\Entity\RateSource;
use App\Exceptions\WrongCurrencyCodeException;
use App\Repository\ExchangeRateRepository;
use App\Repository\RateSourceRepository;
use App\Service\ExchangeRateService;
use DateTime;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class ExchangeRateServiceTest extends KernelTestCase {

    private ExchangeRateRepository $exchangeRateRepositoryMock;
    private RateSourceRepository $rateSourceRepositoryMock;
    private ExchangeRateService $exchangeRateService;

    protected function setUp(): void {
        $this->exchangeRateRepositoryMock = static::getContainer()->get(EntityManagerInterface::class)->getRepository(ExchangeRate::class);
        $this->rateSourceRepositoryMock = static::getContainer()->get(EntityManagerInterface::class)->getRepository(RateSource::class);

        $this->exchangeRateService = new ExchangeRateService(
            $this->exchangeRateRepositoryMock,
            $this->rateSourceRepositoryMock
        );
    }

    public function testConvertCurrencyWithDirectRate() {
        $result = $this->exchangeRateService->convertCurrency(100, 'USD', 'EUR');
        $this->assertEquals(92.83327, $result);
    }

    public function testConvertCurrencyWithInverseRate() {
        $result = $this->exchangeRateService->convertCurrency(100, 'EUR', 'USD');
        $this->assertEquals(107.72, round($result, 2));
    }

    public function testConvertCurrencyWithCrossRateSameBaseCurrency() {
        $result = $this->exchangeRateService->convertCurrency(100, 'USD', 'JPY');
        $this->assertEquals(15241.36651, $result);
    }

    public function testConvertCurrencyWithCrossRates() {
        $result = $this->exchangeRateService->convertCurrency(100, 'ISK', 'BYN');
        $this->assertEquals(0.18729, $result);
    }

    public function testConvertCurrencyThrowsExceptionWhenRateNotFound() {
        $this->expectException(WrongCurrencyCodeException::class);

        $this->exchangeRateService->convertCurrency(100, 'ABC', 'DEF');
    }

    public function testConstructorThrowsExceptionWhenNoDefaultRateSource() {
        $rateSourceRepositoryMock = $this->createMock(RateSourceRepository::class);
        $rateSourceRepositoryMock->method('findOneBy')
            ->with(['isDefault' => true])
            ->willReturn(null);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No default rate source configured in the database.');

        new ExchangeRateService(
            $this->exchangeRateRepositoryMock,
            $rateSourceRepositoryMock
        );
    }
}
