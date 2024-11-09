<?php

namespace App\Entity;

use App\Repository\RateSourceRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: RateSourceRepository::class)]
#[ORM\Index(name: 'idx_rate_sources_name', columns: ['name'])]
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
    private ?bool $is_default = null;

    #[ORM\Column(length: 3)]
    private ?string $base_currency = null;

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

    public function isDefault(): ?bool {
        return $this->is_default;
    }

    public function setIsDefault(bool $is_default): static {
        $this->is_default = $is_default;

        return $this;
    }

    public function getBaseCurrency(): ?string {
        return $this->base_currency;
    }

    public function setBaseCurrency(string $base_currency): static {
        $this->base_currency = $base_currency;

        return $this;
    }
}
