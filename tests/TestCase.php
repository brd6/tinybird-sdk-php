<?php

declare(strict_types=1);

namespace Brd6\Test\TinybirdSdk;

use Hamcrest\Util;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use Mockery\MockInterface;
use PHPUnit\Framework\TestCase as PHPUnitTestCase;

abstract class TestCase extends PHPUnitTestCase
{
    use MockeryPHPUnitIntegration;

    public static function setUpBeforeClass(): void
    {
        Util::registerGlobalFunctions();
    }

    /**
     * @template T
     * @param class-string<T> $class
     * @return T&MockInterface
     */
    protected function mockery(string $class): mixed
    {
        /** @var T&MockInterface */
        return Mockery::mock($class);
    }
}
