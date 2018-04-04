<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use Http\Message\UriFactory\GuzzleUriFactory;
use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Request;
use TechDeCo\ElasticApmAgent\Message\Socket;
use TechDeCo\ElasticApmAgent\Message\Url;

final class RequestTest extends TestCase
{
    public function testAll(): void
    {
        $socket = (new Socket())->thatIsEncrypted();
        $uri    = (new GuzzleUriFactory())->createUri('http://foo.bar/bla');
        $url    = Url::fromUri($uri);
        $actual = (new Request('POST', $url))
            ->withBody('{}')
            ->withEnvironmentVariable('name', 'alloy')
            ->withHeader('content-type', 'application/json')
            ->onHttpVersion('2.0')
            ->onSocket($socket)
            ->withCookie('remember-me', 'yes')
            ->jsonSerialize();

        $expected = [
            'body' => '{}',
            'env' => ['name' => 'alloy'],
            'headers' => ['content-type' => 'application/json'],
            'http_version' => '2.0',
            'method' => 'POST',
            'socket' => ['encrypted' => true],
            'url' => [
                'raw' => 'http://foo.bar/bla',
                'protocol' => 'http',
                'full' => 'http://foo.bar/bla',
                'hostname' => 'foo.bar',
                'pathname' => '/bla',
            ],
            'cookies' => ['remember-me' => 'yes'],
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $uri    = (new GuzzleUriFactory())->createUri('http://foo.bar/bla');
        $url    = Url::fromUri($uri);
        $actual = (new Request('POST', $url))->jsonSerialize();

        $expected = [
            'method' => 'POST',
            'url' => [
                'raw' => 'http://foo.bar/bla',
                'protocol' => 'http',
                'full' => 'http://foo.bar/bla',
                'hostname' => 'foo.bar',
                'pathname' => '/bla',
            ],
        ];

        self::assertEquals($expected, $actual);
    }
}
