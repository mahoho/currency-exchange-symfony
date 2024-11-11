<?php

namespace App\Entity;

use App\Repository\ExchangeRateRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExchangeRateRepository::class)]
#[ORM\Index(name: 'idx_exchange_rates_date', columns: ['date'])]
#[ORM\Table(name: 'exchange_rates')]
class ExchangeRate {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 30, scale: 5)]
    private ?float $rate = null;

    #[ORM\Column(type: 'string', length: 3)]
    private ?string $currencyCode; // New field to store the base currency

    #[ORM\Column(type: 'string', length: 3)]
    private ?string $baseCurrencyCode; // New field to store the base currency

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $date = null;

    #[ORM\ManyToOne(targetEntity: RateSource::class)]
    private ?RateSource $source = null;

    private ?int $sourceId = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getRate(): ?float {
        return $this->rate;
    }

    public function setRate(float $rate): static {
        $this->rate = $rate;

        return $this;
    }

    public function getDate(): ?\DateTimeInterface {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): static {
        $this->date = $date;

        return $this;
    }

    public function getSource(): ?RateSource {
        return $this->source;
    }

    public function setSource(?RateSource $source): static {
        $this->source = $source;

        return $this;
    }

    public function getCurrencyCode(): ?string {
        return $this->currencyCode;
    }

    public function setCurrencyCode(?string $currencyCode): void {
        $this->currencyCode = $currencyCode;
    }

    public function getBaseCurrencyCode(): ?string {
        return $this->baseCurrencyCode;
    }

    public function setBaseCurrencyCode(?string $baseCurrencyCode): void {
        $this->baseCurrencyCode = $baseCurrencyCode;
    }

    public function setSourceId(?int $sourceId): void {
        $this->sourceId = $sourceId;
    }

    public function getSourceId(): ?int {
        return $this->sourceId;
    }
}
