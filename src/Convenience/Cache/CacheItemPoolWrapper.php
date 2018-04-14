<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransactionEnricher;
use TechDeCo\ElasticApmAgent\Convenience\Util\Stopwatch;
use TechDeCo\ElasticApmAgent\Message\Span;
use Traversable;
use function implode;

final class CacheItemPoolWrapper implements CacheItemPoolInterface, OpenTransactionEnricher
{
    /**
     * @var CacheItemPoolInterface
     */
    private $pool;

    /**
     * @var OpenTransaction
     */
    private $transaction;

    /**
     * @var string
     */
    private $type;

    /**
     * @param CacheItemPoolInterface $pool The pool that will be wrapped
     * @param string                 $type The type of cache, for example redis, that will be reported in the span
     */
    public function __construct(
        CacheItemPoolInterface $pool,
        string $type
    ) {
        $this->pool = $pool;
        $this->type = $type;
    }

    public function setOpenTransaction(OpenTransaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    /**
     * @param string $key
     * @throws InvalidArgumentException
     */
    public function getItem($key): CacheItemInterface
    {
        try {
            $start = Stopwatch::start();

            return $this->pool->getItem($key);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $key,
                $this->transaction->getStartOffset(),
                $this->type . '.get-item'
            ));
        }
    }

    /**
     * @param string[] $keys
     * @throws InvalidArgumentException
     * @return array|Traversable|CacheItemInterface[]
     */
    public function getItems(array $keys = [])
    {
        try {
            $start = Stopwatch::start();

            return $this->pool->getItems($keys);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                implode(',', $keys),
                $this->transaction->getStartOffset(),
                $this->type . '.get-items'
            ));
        }
    }

    /**
     * @param string $key
     * @throws InvalidArgumentException
     */
    public function hasItem($key): ?bool
    {
        try {
            $start = Stopwatch::start();

            return $this->pool->hasItem($key);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $key,
                $this->transaction->getStartOffset(),
                $this->type . '.has-item'
            ));
        }
    }

    public function clear(): ?bool
    {
        try {
            $start = Stopwatch::start();

            return $this->pool->clear();
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                '',
                $this->transaction->getStartOffset(),
                $this->type . '.clear'
            ));
        }
    }

    /**
     * @param string $key
     * @throws InvalidArgumentException
     */
    public function deleteItem($key): ?bool
    {
        try {
            $start = Stopwatch::start();

            return $this->pool->deleteItem($key);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $key,
                $this->transaction->getStartOffset(),
                $this->type . '.delete-item'
            ));
        }
    }

    /**
     * @param string[] $keys
     * @throws InvalidArgumentException
     */
    public function deleteItems(array $keys): ?bool
    {
        try {
            $start = Stopwatch::start();

            return $this->pool->deleteItems($keys);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                implode(',', $keys),
                $this->transaction->getStartOffset(),
                $this->type . '.delete-items'
            ));
        }
    }

    public function save(CacheItemInterface $item): ?bool
    {
        try {
            $start = Stopwatch::start();

            return $this->pool->save($item);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $item->getKey(),
                $this->transaction->getStartOffset(),
                $this->type . '.save'
            ));
        }
    }

    public function saveDeferred(CacheItemInterface $item): ?bool
    {
        try {
            $start = Stopwatch::start();

            return $this->pool->saveDeferred($item);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $item->getKey(),
                $this->transaction->getStartOffset(),
                $this->type . '.save-deferred'
            ));
        }
    }

    public function commit(): ?bool
    {
        try {
            $start = Stopwatch::start();

            return $this->pool->commit();
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                'commit',
                $this->transaction->getStartOffset(),
                $this->type . '.commit'
            ));
        }
    }
}
