<?php

namespace App\Tests\Service\PullRates;

use App\Service\PullRates\CbrPullRatesService;
use App\Service\PullRates\EcbPullRatesService;
use App\Service\PullRates\PullRatesFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class PullRatesFactoryTest extends TestCase {
    public function testGetServiceReturnsCorrectService() {
        // Create mocks for services
        $ecbServiceMock = $this->createMock(EcbPullRatesService::class);
        $ecbServiceMock->method('supports')->with('ECB')->willReturn(true);

        $cbrServiceMock = $this->createMock(CbrPullRatesService::class);
        $cbrServiceMock->method('supports')->with('ECB')->willReturn(false);

        $rateSources = [$ecbServiceMock, $cbrServiceMock];

        $factory = new PullRatesFactory($rateSources);

        $service = $factory->getService('ECB');
        $this->assertSame($ecbServiceMock, $service);
    }

    public function testGetServiceThrowsExceptionWhenNoServiceFound() {
        $ecbServiceMock = $this->createMock(EcbPullRatesService::class);
        $ecbServiceMock->method('supports')->with('Unknown')->willReturn(false);

        $cbrServiceMock = $this->createMock(CbrPullRatesService::class);
        $cbrServiceMock->method('supports')->with('Unknown')->willReturn(false);

        $rateSources = [$ecbServiceMock, $cbrServiceMock];

        $factory = new PullRatesFactory($rateSources);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('No service found for rates source: Unknown');

        $factory->getService('Unknown');
    }
}

