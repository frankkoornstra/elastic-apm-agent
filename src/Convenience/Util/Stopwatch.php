<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Util;

use function microtime;

final class Stopwatch
{
    /**
     * @return float Current time in microseconds
     */
    public static function start(): float
    {
        return microtime(true) * 1000;
    }

    /**
     * @param float $start Start in microseconds
     * @return float Duraction in microseconds
     */
    public static function stop(float $start): float
    {
        return (microtime(true) * 1000) - $start;
    }
}
