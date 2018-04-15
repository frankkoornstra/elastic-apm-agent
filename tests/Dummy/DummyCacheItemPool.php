<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Dummy;

use Cache\Adapter\Common\Exception\InvalidArgumentException as InvalidArgumentExceptionImpl;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use function usleep;

final class DummyCacheItemPool implements CacheItemPoolInterface
{
    /**
     * @var CacheItemInterface
     */
    private $item;

    /**
     * @var int
     */
    private $busyTime;

    /**
     * @var bool
     */
    private $throwsException;

    public function __construct(CacheItemInterface $item, int $busyTime, ?bool $throwsException = false)
    {
        $this->item            = $item;
        $this->busyTime        = $busyTime;
        $this->throwsException = $throwsException;
    }

    /**
     * @throws InvalidArgumentException
     */
    private function createResponse(): CacheItemInterface
    {
        if ($this->throwsException) {
            throw new InvalidArgumentExceptionImpl('test');
        }

        usleep($this->busyTime * 1000);

        return $this->item;
    }

    /**
     * @param string $key
     * @throws InvalidArgumentException
     * @return CacheItemInterface
     */
    public function getItem($key)
    {
        return $this->createResponse();
    }

    /**
     * @param string[] $keys
     * @throws InvalidArgumentException
     * @return array|\Traversable
     */
    public function getItems(array $keys = [])
    {
        return [
            $this->createResponse(),
        ];
    }

    /**
     * @param string $key
     * @throws InvalidArgumentException
     * @return bool
     */
    public function hasItem($key)
    {
        $this->createResponse();

        return true;
    }

    /**
     * @return bool
     */
    public function clear()
    {
        usleep($this->busyTime * 1000);

        return true;
    }

    /**
     * @param string $key
     * @throws InvalidArgumentException
     * @return bool
     */
    public function deleteItem($key)
    {
        $this->createResponse();

        return true;
    }

    /**
     * @param string[] $keys
     * @throws InvalidArgumentException
     * @return bool
     */
    public function deleteItems(array $keys)
    {
        $this->createResponse();

        return true;
    }

    /**
     * @return bool
     */
    public function save(CacheItemInterface $item)
    {
        usleep($this->busyTime * 1000);

        return true;
    }

    /**
     * @return bool
     */
    public function saveDeferred(CacheItemInterface $item)
    {
        usleep($this->busyTime * 1000);

        return true;
    }

    /**
     * @return bool
     */
    public function commit()
    {
        usleep($this->busyTime * 1000);

        return true;
    }
}
