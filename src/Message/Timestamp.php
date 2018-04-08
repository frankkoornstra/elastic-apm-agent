<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Message;

use DateTimeImmutable;
use JsonSerializable;

final class Timestamp extends DateTimeImmutable implements JsonSerializable
{
    public function jsonSerialize(): string
    {
        return $this->__toString();
    }

    public function __toString(): string
    {
        $me = clone $this;

        return $me->format('Y-m-d\TH:i:s.u\Z');
    }
}
