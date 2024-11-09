<?php

namespace App\Repository;

use App\Entity\RateSource;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<RateSource>
 */
class RateSourceRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, RateSource::class);
    }

    /**
     * @return RateSource|null
     */
    public function findByName(string $value): ?RateSource {
        return $this->createQueryBuilder('r')
            ->andWhere('r.name = :name')
            ->setParameter('name', $value)
            ->setMaxResults(1)
            ->getQuery()
            ->getResult()[0] ?? null;
    }
}
