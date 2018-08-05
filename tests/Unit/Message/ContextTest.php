<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use GuzzleHttp\Psr7\Uri;
use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Context;
use TechDeCo\ElasticApmAgent\Message\Request;
use TechDeCo\ElasticApmAgent\Message\Response;
use TechDeCo\ElasticApmAgent\Message\Url;
use TechDeCo\ElasticApmAgent\Message\User;

final class ContextTest extends TestCase
{
    public function testAll(): void
    {
        $actual = (new Context())
            ->withCustomVariable('a', 'behemoth')
            ->withRequest(new Request('GET', Url::fromUri(new Uri('http://gaia.prime'))))
            ->withResponse((new Response())->resultingInStatusCode(200))
            ->withTag('b', 'charger')
            ->withUser((new User())->withId(9))
            ->jsonSerialize();

        $expected = [
            'custom' => ['a' => 'behemoth'],
            'response' => ['status_code' => 200],
            'request' => [
                'method' => 'GET',
                'url' => [
                    'raw' => 'http://gaia.prime',
                    'protocol' => 'http',
                    'full' => 'http://gaia.prime',
                    'hostname' => 'gaia.prime',
                    'pathname' => '',
                ],
            ],
            'user' => ['id' => 9],
            'tags' => ['b' => 'charger'],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new Context())->jsonSerialize();

        self::assertEmpty($actual);
    }

    public function testGetters(): void
    {
        $request  = new Request('GET', Url::fromUri(new Uri('http://gaia.prime')));
        $response = (new Response())->resultingInStatusCode(200);
        $context  = (new Context())
            ->withCustomVariable('a', 'behemoth')
            ->withRequest($request)
            ->withResponse($response);

        self::assertSame($request, $context->getRequest());
        self::assertSame($response, $context->getResponse());
    }
}
