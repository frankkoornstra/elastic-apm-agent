<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Message;

use PHPUnit\Framework\TestCase;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\VersionedName;

final class ServiceTest extends TestCase
{
    public function testFromAgentAndHash(): void
    {
        $actual = (new Service(new VersionedName('alloy', '1'), 'varl'))
            ->usingFramework(new VersionedName('sona', '2'))
            ->usingLanguage(new VersionedName('nora', '3'))
            ->inEnvironment('prod')
            ->withRuntime(new VersionedName('brom', '4'))
            ->atVersion('7')
            ->jsonSerialize();

        $expected = [
            'agent' => [
                'name' => 'alloy',
                'version' => '1',
            ],
            'framework' => [
                'name' => 'sona',
                'version' => '2',
            ],
            'language' => [
                'name' => 'nora',
                'version'=> '3',
            ],
            'name' => 'varl',
            'environment' => 'prod',
            'runtime' => [
                'name' => 'brom',
                'version' => '4',
            ],
            'version' => '7',
        ];

        self::assertEquals($expected, $actual);
    }

    public function testFiltersEmpty(): void
    {
        $actual = (new Service(new VersionedName('alloy', '1'), 'varl'))->jsonSerialize();

        $expected = [
            'agent' => [
                'name' => 'alloy',
                'version' => '1',
            ],
            'name' => 'varl',
        ];

        self::assertEquals($expected, $actual);
    }
}
