<?php

namespace App\DataFixtures;

use App\Entity\RateSource;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use function Symfony\Component\String\u;

class RateSourcesFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $data = [
            [
                'name' => "ECB",
                'url' => "https://www.ecb.europa.eu/stats/eurofxref/eurofxref-daily.xml",
                'base_currency' => 'EUR',
                'is_default' => 1
            ],
            [
                'name' => "ECB",
                'url' => "https://www.cbr.ru/scripts/XML_daily.asp",
                'base_currency' => 'RUB',
                'is_default' => 0
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
}
