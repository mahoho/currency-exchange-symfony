<?php

namespace App\Service\PullRates;

use InvalidArgumentException;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;

class PullRatesFactory {
    public function __construct(
        #[AutowireIterator('app_pull_rates_source')]
        private readonly iterable $rateSources
    ) {
    }

    /**
     * Returns the appropriate PullRatesService for the specified provider code.
     *
     * @param string $providerName
     * @return PullRatesService
     * @throws InvalidArgumentException
     */
    public function getService(string $providerName): PullRatesService {
        foreach ($this->rateSources as $service) {
            if ($service->supports($providerName)) {
                return $service;
            }
        }

        throw new InvalidArgumentException("No service found for rates source: {$providerName}");
    }
}
