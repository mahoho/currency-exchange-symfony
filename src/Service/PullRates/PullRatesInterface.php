<?php

namespace App\Service\PullRates;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag(name: "app_pull_rates_source")]
interface PullRatesInterface {
    public function supports(string $providerName): bool;

    public function fetchRates(): array;
}
