<?php

namespace App\Service\PullRates;

use App\DTO\RateDTO;
use App\Entity\ExchangeRate;
use App\Entity\RateSource;
use App\Repository\ExchangeRateRepository;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Symfony\Contracts\HttpClient\HttpClientInterface;

abstract class PullRatesService implements PullRatesInterface {
    protected RateSource $rateSource;
    protected string $rateSourceName;

    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly HttpClientInterface    $httpClient,
        protected readonly ExchangeRateRepository $repository,
    ) {
        if(!$this->rateSourceName) {
            throw new InvalidArgumentException("rateSourceName not set for " . get_class($this));
        }

        $this->rateSource = $this->entityManager->getRepository(RateSource::class)->findOneBy(['name' => $this->rateSourceName]);
    }

    /**
     * @param RateDTO[] $rates
     */
    public function saveRates(array $rates): void {
        foreach ($rates as $rateDTO) {
            $existingRate = $this->repository->findOneBy([
                'currencyCode' => $rateDTO->getCurrencyCode(),
                'date'         => $rateDTO->getDate(),
                'baseCurrencyCode' => $rateDTO->getBaseCurrencyCode(),
                'source' => $this->rateSource->getId()
            ]);

            if (!$existingRate) {
                $exchangeRate = new ExchangeRate();
                $exchangeRate->setCurrencyCode($rateDTO->getCurrencyCode());
                $exchangeRate->setRate($rateDTO->getRate());
                $exchangeRate->setBaseCurrencyCode($rateDTO->getBaseCurrencyCode());
                $exchangeRate->setDate($rateDTO->getDate());
                $exchangeRate->setSource($this->rateSource);

                $this->entityManager->persist($exchangeRate);
            } else {
                $existingRate->setRate($rateDTO->getRate());
                $this->entityManager->persist($existingRate);
            }
        }

        $this->entityManager->flush();
    }

    abstract public function fetchRates(): array;

    abstract public function supports(string $providerName): bool;
}
