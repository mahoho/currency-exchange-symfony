<?php

namespace App\Repository;

use App\Entity\ExchangeRate;
use App\Entity\RateSource;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query\Parameter;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ExchangeRate>
 */
class ExchangeRateRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, ExchangeRate::class);
    }

    /**
     * @param string $currencyCode
     * @param string $baseCurrencyCode
     * @param DateTime $date
     * @param RateSource $rateSource
     * @return ExchangeRate|null
     */
    public function getByAnyDifferentSource(string $currencyCode, string $baseCurrencyCode, DateTime $date, RateSource $rateSource) : ?ExchangeRate {
        $q = $this->createQueryBuilder('s')
            ->andWhere('s.currencyCode = :currency_code')
            ->andWhere('s.baseCurrencyCode = :base_currency_code')
            ->andWhere('s.date = :date')
            ->andWhere('s.source != :source')
            ->setMaxResults(1)
            ->setParameters(new ArrayCollection([
                new Parameter('currency_code', $currencyCode),
                new Parameter('base_currency_code', $baseCurrencyCode),
                new Parameter('date', $date->format('Y-m-d')),
                new Parameter('source', $rateSource),
            ]))->getQuery();

        return $q->execute()[0] ?? null;
    }
}
