<?php

namespace App\DataFixtures;

use App\Entity\ExchangeRate;
use App\Entity\RateSource;
use App\Repository\RateSourceRepository;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use function Symfony\Component\String\u;

class ExchangeRateFixture extends Fixture implements FixtureGroupInterface, OrderedFixtureInterface{
    public function __construct(
        private readonly RateSourceRepository   $rateSourceRepository,
    ) {
    }

    public function load(ObjectManager $manager): void {
        $data = <<<JSON
[
  {
    "source_id": 1,
    "rate": 1.07720,
    "currency_code": "USD",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 164.18000,
    "currency_code": "JPY",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 1.95580,
    "currency_code": "BGN",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 25.20800,
    "currency_code": "CZK",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 7.45730,
    "currency_code": "DKK",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 0.83188,
    "currency_code": "GBP",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 406.68000,
    "currency_code": "HUF",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 4.32550,
    "currency_code": "PLN",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 148.70000,
    "currency_code": "ISK",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 4.02400,
    "currency_code": "ILS",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 21.53490,
    "currency_code": "MXN",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 1,
    "rate": 4.72080,
    "currency_code": "MYR",
    "base_currency_code": "EUR"
  },
  {
    "source_id": 2,
    "rate": 57.54910,
    "currency_code": "AZN",
    "base_currency_code": "RUB"
  },
  {
    "source_id": 2,
    "rate": 0.25256,
    "currency_code": "AMD",
    "base_currency_code": "RUB"
  },
  {
    "source_id": 2,
    "rate": 29.36880,
    "currency_code": "BYN",
    "base_currency_code": "RUB"
  },
  {
    "source_id": 2,
    "rate": 0.00403,
    "currency_code": "VND",
    "base_currency_code": "RUB"
  },
  {
    "source_id": 2,
    "rate": 35.79970,
    "currency_code": "GEL",
    "base_currency_code": "RUB"
  },
  {
    "source_id": 2,
    "rate": 26.63950,
    "currency_code": "AED",
    "base_currency_code": "RUB"
  },
  {
    "source_id": 2,
    "rate": 105.45100,
    "currency_code": "EUR",
    "base_currency_code": "RUB"
  },
  {
    "source_id": 2,
    "rate": 1.98430,
    "currency_code": "EGP",
    "base_currency_code": "RUB"
  }
]

JSON;
        $now = new DateTime();

        $rateSources = $this->rateSourceRepository->findAll();

        $keyBy = [];

        foreach ($rateSources as $rateSource) {
            $keyBy[$rateSource->getId()] = $rateSource;
        }
        unset($rateSources);


        foreach (json_decode($data, true) as $row) {
            $exchangeRate = new ExchangeRate();
            // we have no fill in Doctrine Entities like in Eloquent, so
            foreach ($row as $field => $value) {
                $method = 'set' . u($field)->camel()->title()->toString();
                $exchangeRate->$method($value);
            }

            $exchangeRate->setDate($now);
            $exchangeRate->setSource($keyBy[$row['source_id']]);

            $manager->persist($exchangeRate);
        }

        $manager->flush();
    }

    public static function getGroups(): array {
        return ['exchange_rates'];
    }

    public function getOrder(): int {
        return 2; // smaller means sooner
    }
}
