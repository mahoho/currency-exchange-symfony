<?php

namespace App\Service\PullRates;

use App\DTO\RateDTO;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class EcbPullRatesService extends PullRatesService {
    protected string $rateSourceName = 'ECB';

    /**
     * @return array<integer, RateDTO>
     * @throws \Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface
     * @throws \Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface
     */
    public function fetchRates(): array {
        try {
            $response = $this->httpClient->request('GET', $this->rateSource->getUrl());
            $data = $response->getContent();
            $xml = new SimpleXMLElement($data);
            $date = new \DateTime(); // Use current date; update as needed if a specific date is provided in the data

            $rates = [];
            foreach ($xml->Cube->Cube->Cube as $rate) {
                $currency = (string)$rate['currency'];
                $value = filter_var($rate['rate'], FILTER_VALIDATE_FLOAT);

                $rates[] = new RateDTO(
                    $currency,
                    $value,
                    $this->rateSource->getBaseCurrencyCode(), // Base currency is obtained from RateSource entity
                    $date
                );
            }

            return $rates;
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException("Failed to fetch rates from ECB: " . $e->getMessage());
        }
    }

    public function supports(string $providerName): bool {
        return $providerName === 'ECB';
    }
}
