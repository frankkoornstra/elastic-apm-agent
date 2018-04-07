<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\ClientConfiguration;

final class ClientConfigurationTest extends TestCase
{
    public function testAll(): void
    {
        $config = (new ClientConfiguration('http://foo.bar'))
            ->authenticatedByToken('alloy');

        self::assertStringStartsWith('http://foo.bar/', $config->getTransactionsEndpoint());
        self::assertStringStartsWith('http://foo.bar/', $config->getErrorsEndpoint());
        self::assertSame('alloy', $config->getToken());
        self::assertTrue($config->needsAuthentication());
    }

    public function testDoesNotNeedAuthenction(): void
    {
        $config = new ClientConfiguration('http://foo.bar');

        self::assertFalse($config->needsAuthentication());
    }
}
