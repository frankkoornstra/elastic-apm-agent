<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use Http\Message\UriFactory\GuzzleUriFactory;
use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Url;

final class UrlTest extends TestCase
{
    public function testAll(): void
    {
        $uri      = (new GuzzleUriFactory())->createUri('https://foo.bar:444/apath?bar=baz#bla');
        $actual   = Url::fromUri($uri)->jsonSerialize();
        $expected = [
            'raw' => 'https://foo.bar:444/apath?bar=baz#bla',
            'protocol' => 'https',
            'full' => 'https://foo.bar:444/apath?bar=baz#bla',
            'hostname' => 'foo.bar',
            'port' => '444',
            'pathname' => '/apath',
            'search' => 'bar=baz',
            'hash' => 'bla',
        ];

        self::assertEquals($expected, $actual);
    }
}
