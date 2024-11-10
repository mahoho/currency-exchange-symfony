<?php

namespace App\DTO;

class RateDTO {
    private string $currencyCode;
    private float $rate;
    private string $baseCurrencyCode;
    private \DateTimeInterface $date;

    public function __construct(string $currencyCode, float $rate, string $baseCurrencyCode, \DateTimeInterface $date) {
        $this->currencyCode = $currencyCode;
        $this->rate = $rate;
        $this->baseCurrencyCode = $baseCurrencyCode;
        $this->date = $date;
    }

    public function getCurrencyCode(): string {
        return $this->currencyCode;
    }

    public function getRate(): float {
        return $this->rate;
    }

    public function getBaseCurrencyCode(): string {
        return $this->baseCurrencyCode;
    }

    public function getDate(): \DateTimeInterface {
        return $this->date;
    }
}
