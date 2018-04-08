<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use Ramsey\Uuid\UuidInterface;
use RuntimeException;

final class InvalidUuid implements UuidInterface
{
    public function serialize(): void
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * @param mixed $serialized
     */
    public function unserialize($serialized): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function compareTo(UuidInterface $other): void
    {
        throw new RuntimeException('Not implemented');
    }

    /**
     * @param mixed $other
     */
    public function equals($other): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getBytes(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getNumberConverter(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getFieldsHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getClockSeqHiAndReservedHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getClockSeqLowHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getClockSequenceHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getDateTime(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getInteger(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getLeastSignificantBitsHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getMostSignificantBitsHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getNodeHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getTimeHiAndVersionHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getTimeLowHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getTimeMidHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getTimestampHex(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getUrn(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getVariant(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function getVersion(): void
    {
        throw new RuntimeException('Not implemented');
    }

    public function toString(): string
    {
        return '';
    }

    public function jsonSerialize(): string
    {
        return $this->toString();
    }
}
