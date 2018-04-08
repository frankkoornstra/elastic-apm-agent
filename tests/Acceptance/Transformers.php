<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use Behat\Behat\Context\Context;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use TechDeCo\ElasticApmAgent\Message\Timestamp;

final class Transformers implements Context
{
    /**
     * @Transform :id
     */
    public function transformId(string $id): UuidInterface
    {
        return Uuid::fromString($id);
    }

    /**
     * @Transform :duration
     */
    public function transformDuration(string $duration): float
    {
        return (float) $duration;
    }

    /**
     * @Transform :timestamp
     */
    public function transformDate(string $date): Timestamp
    {
        return new Timestamp($date);
    }
}
