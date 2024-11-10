<?php

namespace App\DataFixtures;

use App\Entity\RateSource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use function Symfony\Component\String\u;

class RateSourcesFixture extends Fixture {
    public function load(ObjectManager $manager): void {
        $data = [
            [
                'name'             => "ECB",
                'url'              => "https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml",
                'baseCurrencyCode' => 'EUR',
                'isDefault'        => 1
            ],
            [
                'name'             => "CBR",
                'url'              => "https://www.cbr.ru/scripts/XML_daily.asp",
                'baseCurrencyCode' => 'RUB',
                'isDefault'        => 0
            ]
        ];

        foreach ($data as $datum) {
            $rateSource = new RateSource();
            // we have no fill in Doctrine Entities like in Eloquent, so
            foreach ($datum as $field => $value) {
                $method = 'set' . u($field)->camel()->title()->toString();
                $rateSource->$method($value);
            }

            $manager->persist($rateSource);
        }

        $manager->flush();
    }

    public static function getGroups(): array {
        return ['rates_sources'];
    }
}
