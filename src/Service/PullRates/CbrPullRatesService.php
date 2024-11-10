<?php

namespace App\Service\PullRates;

use App\DTO\RateDTO;
use SimpleXMLElement;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

class CbrPullRatesService extends PullRatesService {
    protected string $rateSourceName = 'CBR';

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
            foreach ($xml->Valute as $rate) {
                $currency = (string)$rate->CharCode;
                $value = filter_var($rate->Value, FILTER_VALIDATE_FLOAT);
                $nominal = filter_var($rate->Nominal, FILTER_VALIDATE_FLOAT);
                $valueConverted = (float)bcdiv($value, $nominal);

                $rates[] = new RateDTO(
                    $currency,
                    $valueConverted,
                    $this->rateSource->getBaseCurrencyCode(), // Base currency is obtained from RateSource entity
                    $date
                );
            }

            return $rates;
        } catch (TransportExceptionInterface $e) {
            throw new \RuntimeException("Failed to fetch rates from CBR: " . $e->getMessage());
        }
    }

    public function supports(string $providerName): bool {
        return $providerName === 'CBR';
    }
}
