<?php

namespace App\Tests\Controller;

use App\Service\ExchangeRateService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ExchangeControllerTest extends WebTestCase {
    private $client;
    private $exchangeRateServiceMock;

    protected function setUp(): void {
        $this->client = static::createClient();

        $this->exchangeRateServiceMock = $this->createMock(ExchangeRateService::class);
    }

    public function testConvertSuccess() {
        $this->exchangeRateServiceMock
            ->method('convertCurrency')
            ->willReturn(85.0);

        static::getContainer()->set(ExchangeRateService::class, $this->exchangeRateServiceMock);

        $this->client->request('GET', '/api/exchange/convert', [
            'amount' => '100',
            'from'   => 'USD',
            'to'     => 'EUR',
        ]);

        self::assertResponseIsSuccessful();
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $expectedJson = json_encode([
            'amount'          => 100.0,
            'from'            => 'USD',
            'to'              => 'EUR',
            'convertedAmount' => 85.0,
        ]);

        self::assertJsonStringEqualsJsonString($expectedJson, $this->client->getResponse()->getContent());
    }

    public function testConvertMissingParameters() {
        $this->client->request('GET', '/api/exchange/convert');

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $expectedJson = json_encode(['error' => 'Missing required parameters: amount, from, to.']);

        self::assertJsonStringEqualsJsonString($expectedJson, $this->client->getResponse()->getContent());
    }

    public function testConvertInvalidAmount() {
        $this->client->request('GET', '/api/exchange/convert', [
            'amount' => '-100',
            'from'   => 'USD',
            'to'     => 'EUR',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $expectedJson = json_encode(['error' => 'Amount must be a positive number.']);

        self::assertJsonStringEqualsJsonString($expectedJson, $this->client->getResponse()->getContent());
    }

    public function testConvertThrowsException() {
        $this->exchangeRateServiceMock
            ->method('convertCurrency')
            ->willThrowException(new \Exception('Conversion error'));

        static::getContainer()->set(ExchangeRateService::class, $this->exchangeRateServiceMock);

        $this->client->request('GET', '/api/exchange/convert', [
            'amount' => '100',
            'from'   => 'USD',
            'to'     => 'EUR',
        ]);

        self::assertResponseStatusCodeSame(Response::HTTP_BAD_REQUEST);

        $expectedJson = json_encode(['error' => 'Conversion error']);

        self::assertJsonStringEqualsJsonString($expectedJson, $this->client->getResponse()->getContent());
    }
}
