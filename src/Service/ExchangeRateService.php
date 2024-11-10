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
    private ?RateSource $rateSource;

    public function __construct(
        private readonly ExchangeRateRepository $exchangeRateRepository,
        private readonly RateSourceRepository   $rateSourceRepository,
    ) {

        $this->rateSource = $this->rateSourceRepository->findOneBy(['isDefault' => 1]);

        if(!$this->rateSource) {
            throw new InvalidArgumentException("No default rate source configured in database");
        }
    }

    /**
     * Converts an amount from one currency to another.
     *
     * @param float $amount
     * @param string $fromCurrencyCode
     * @param string $toCurrencyCode
     * @return float
     * @throws WrongCurrencyCodeException
     */
    public function convertCurrency(float $amount, string $fromCurrencyCode, string $toCurrencyCode): float {
        $date = new DateTime();

        $fromRateEntry = $this->getRateEntryForCurrency($fromCurrencyCode, $date);
        $toRateEntry = $this->getRateEntryForCurrency($toCurrencyCode, $date);

        if ($fromRateEntry === null || $toRateEntry === null) {
            throw new WrongCurrencyCodeException("Exchange rate not found for '{$fromCurrencyCode}' or '{$toCurrencyCode}' on date {$date->format('Y-m-d')}.");
        }

        $fromRate = $fromRateEntry->getRate();
        $fromBaseCurrency = $fromRateEntry->getBaseCurrencyCode();
        $toRate = $toRateEntry->getRate();
        $toBaseCurrency = $toRateEntry->getBaseCurrencyCode();

        // Case 1: Both currencies have the same base currency
        if ($fromBaseCurrency === $toBaseCurrency) {
            return round($amount / $fromRate * $toRate, 2);
        }

        // Case 2: Different base currencies - Convert via a common intermediate base
        if ($fromBaseCurrency === $this->rateSource->defaultBaseCurrency) {
            $fromToEurRate = $fromRate;
        } else {
            $fromToEurRate = 1 / $this->getRateForCurrency($fromBaseCurrency, $this->rateSource->defaultBaseCurrency, $date);
        }

        if ($toBaseCurrency === $this->rateSource->defaultBaseCurrency) {
            $eurToToRate = $toRate;
        } else {
            $eurToToRate = $this->getRateForCurrency($this->rateSource->defaultBaseCurrency, $toBaseCurrency, $date);
        }

        $amountInEur = $amount / $fromToEurRate;
        $convertedAmount = $amountInEur * $eurToToRate;

        // to avoid float precision problem
        return round($convertedAmount, 2);
    }

    /**
     * Retrieves the rate entry for a specific currency and date.
     * If the currency is the base currency, returns a pseudo-entry with rate 1.0.
     *
     * @param string $currencyCode
     * @param DateTime $date
     * @return ExchangeRate|null
     */
    public function getRateEntryForCurrency(string $currencyCode, DateTime $date): ?ExchangeRate {
        if ($currencyCode === $this->rateSource->defaultBaseCurrency) {
            $baseEntry = new ExchangeRate();
            $baseEntry->setCurrencyCode($currencyCode);
            $baseEntry->setRate(1.0);
            $baseEntry->setBaseCurrencyCode($currencyCode);
            $baseEntry->setDate($date);
            $baseEntry->setSource($this->rateSource);

            return $baseEntry;
        }

        return $this->exchangeRateRepository->findOneBy([
            'currencyCode' => $currencyCode,
            'date'         => $date,
            'source'       => $this->rateSource
        ]);
    }

    /**
     * Retrieves the rate for converting one base currency to another.
     *
     * @param string $currencyCode
     * @param string $baseCurrency
     * @param DateTime $date
     * @return float|null
     */
    public function getRateForCurrency(string $currencyCode, string $baseCurrency, DateTime $date): ?float {
        if ($currencyCode === $baseCurrency) {
            return 1.0;
        }

        $exchangeRate = $this->exchangeRateRepository->findOneBy([
            'currencyCode' => $currencyCode,
            'baseCurrency' => $baseCurrency,
            'date'         => $date,
        ]);

        return $exchangeRate ? $exchangeRate->getRate() : null;
    }
}

