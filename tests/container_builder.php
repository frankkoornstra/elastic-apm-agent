<?php
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Lcobucci\DependencyInjection\ContainerBuilder;

return (new ContainerBuilder())
    ->setDumpDir(__DIR__ . '/../tmp/container')
    ->useDevelopmentMode()
    ->addFile(__DIR__ . '/services.xml');
