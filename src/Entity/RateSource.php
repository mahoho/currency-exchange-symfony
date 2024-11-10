<?php

namespace App\Entity;

use App\Repository\RateSourceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: RateSourceRepository::class)]
#[ORM\Index(name: 'idx_rate_sources_name', columns: ['name'])]
#[ORM\Table(name: 'rates_sources')]
#[UniqueEntity(fields: ['name'])]
class RateSource {
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column(length: 1000)]
    private ?string $url = null;

    #[ORM\Column]
    private ?bool $isDefault = null;

    #[ORM\Column(length: 3)]
    private ?string $baseCurrencyCode = null;

    public function getId(): ?int {
        return $this->id;
    }

    public function getName(): ?string {
        return $this->name;
    }

    public function setName(string $name): static {
        $this->name = $name;

        return $this;
    }

    public function getUrl(): ?string {
        return $this->url;
    }

    public function setUrl(string $url): static {
        $this->url = $url;

        return $this;
    }

    public function getIsDefault(): ?bool {
        return $this->isDefault;
    }

    public function setIsDefault(bool $isDefault): static {
        $this->isDefault = $isDefault;

        return $this;
    }

    public function getBaseCurrencyCode(): ?string {
        return $this->baseCurrencyCode;
    }

    public function setBaseCurrencyCode(string $baseCurrencyCode): static {
        $this->baseCurrencyCode = $baseCurrencyCode;

        return $this;
    }
}
