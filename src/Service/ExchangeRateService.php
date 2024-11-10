<?php

namespace App\Service;

use App\Entity\ExchangeRate;
use App\Entity\RateSource;
use App\Exceptions\WrongCurrencyCodeException;
use App\Repository\ExchangeRateRepository;
use App\Repository\RateSourceRepository;
use DateTime;
use InvalidArgumentException;

class ExchangeRateService {
    private ?RateSource $primaryRateSource;

    public function __construct(
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly RateSourceRepository   $rateSourceRepository,
    ) {
        $this->primaryRateSource = $this->rateSourceRepository->findOneBy(['isDefault' => true]);

        if (!$this->primaryRateSource) {
            throw new InvalidArgumentException("No default rate source configured in the database.");
        }
    }

    /**
     * Converts an amount from one currency to another, using primary source and fallback if necessary.
     *
     * @param float $amount
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @return float
     * @throws WrongCurrencyCodeException
     */
    public function convertCurrency(float $amount, string $fromCurrencyCode, string $toCurrencyCode): float {
        $date = new DateTime();

        // Case 1: Try to find a direct rate from fromCurrency to toCurrency
        $directRateEntry = $this->exchangeRateRepository->findOneBy([
            'currencyCode'     => $toCurrencyCode,
            'baseCurrencyCode' => $fromCurrencyCode,
            'date'             => $date,
        ]);

        if ($directRateEntry) {
            $rate = $directRateEntry->getRate();
            $convertedAmount = $amount * $rate;
            return round($convertedAmount, 5);
        }

        // Case 2: Try to find an inverse rate from toCurrency to fromCurrency
        $inverseRateEntry = $this->exchangeRateRepository->findOneBy([
            'currencyCode'     => $fromCurrencyCode,
            'baseCurrencyCode' => $toCurrencyCode,
            'date'             => $date,
        ]);

        if ($inverseRateEntry) {
            $rate = 1 / $inverseRateEntry->getRate();
            $convertedAmount = $amount * $rate;
            return round($convertedAmount, 5);
        }

        // Case 3: Use cross rates
        $fromRateEntry = $this->getRateEntryForCurrency($fromCurrencyCode, $date);
        $toRateEntry = $this->getRateEntryForCurrency($toCurrencyCode, $date);

        if (!$fromRateEntry || !$toRateEntry) {
            throw new WrongCurrencyCodeException("Exchange rate not found for '{$fromCurrencyCode}' or '{$toCurrencyCode}' on date {$date->format('Y-m-d')}.");
        }

        $convertedAmount = $this->calculateCrossRateConversion($amount, $fromRateEntry, $toRateEntry);

        return round($convertedAmount, 5);
    }

    /**
     * @param float $amount
     * @param ExchangeRate $fromRateEntry
     * @param ExchangeRate $toRateEntry
     * @return float
     * @throws WrongCurrencyCodeException
     */
    private function calculateCrossRateConversion(float $amount, ExchangeRate $fromRateEntry, ExchangeRate $toRateEntry): float {
        $fromRate = $fromRateEntry->getRate();
        $fromBaseCurrency = $fromRateEntry->getBaseCurrencyCode();

        $toRate = $toRateEntry->getRate();
        $toBaseCurrency = $toRateEntry->getBaseCurrencyCode();

        if ($fromBaseCurrency === $toBaseCurrency) {
            $convertedAmount = ($amount / $fromRate) * $toRate;
            return $convertedAmount;
        }

        $crossRate = $this->getCrossRate($fromBaseCurrency, $toBaseCurrency);
        $amountInToBaseCurrency = ($amount / $fromRate) * $crossRate;
        $convertedAmount = $amountInToBaseCurrency * $toRate;

        return $convertedAmount;
    }

    /**
     * @param string $fromBaseCurrency
     * @param string $toBaseCurrency
     * @return float
     * @throws WrongCurrencyCodeException
     */
    private function getCrossRate(string $fromBaseCurrency, string $toBaseCurrency): float {
        if ($fromBaseCurrency === $toBaseCurrency) {
            return 1.0;
        }

        $date = new DateTime();

        $directRateEntry = $this->exchangeRateRepository->findOneBy([
            'currencyCode'     => $toBaseCurrency,
            'baseCurrencyCode' => $fromBaseCurrency,
            'date'             => $date,
        ]);

        if ($directRateEntry) {
            return $directRateEntry->getRate();
        }

        $inverseRateEntry = $this->exchangeRateRepository->findOneBy([
            'currencyCode'     => $fromBaseCurrency,
            'baseCurrencyCode' => $toBaseCurrency,
            'date'             => $date,
        ]);

        if ($inverseRateEntry) {
            return 1 / $inverseRateEntry->getRate();
        }

        throw new WrongCurrencyCodeException("Cross rate not found between base currencies '{$fromBaseCurrency}' and '{$toBaseCurrency}' on date {$date->format('Y-m-d')}.");
    }

    /**
     * @param string $currencyCode
     * @param DateTime $date
     * @return ExchangeRate|null
     */
    public function getRateEntryForCurrency(string $currencyCode, DateTime $date): ?ExchangeRate {
        $primarySourceRateEntry = $this->exchangeRateRepository->findOneBy([
            'currencyCode' => $currencyCode,
            'date'         => $date,
            'source'       => $this->primaryRateSource,
        ]);

        if ($primarySourceRateEntry) {
            return $primarySourceRateEntry;
        }

        // Fallback: Try to get the rate from any available source
        return $this->exchangeRateRepository->findOneBy([
            'currencyCode' => $currencyCode,
            'date'         => $date,
        ]);
    }
}
