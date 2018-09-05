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
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            return $this->pool->getItem($key);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $key,
                $offset,
                $this->type . '.get-item'
            ));
        }
    }

    /**
     * @param string[] $keys
     * @throws InvalidArgumentException
     * @return array|Traversable
     */
    public function getItems(array $keys = [])
    {
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            return $this->pool->getItems($keys);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                implode(',', $keys),
                $offset,
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
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            return $this->pool->hasItem($key);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $key,
                $offset,
                $this->type . '.has-item'
            ));
        }
    }

    public function clear(): ?bool
    {
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            return $this->pool->clear();
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                '',
                $offset,
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
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            return $this->pool->deleteItem($key);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $key,
                $offset,
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
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            return $this->pool->deleteItems($keys);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                implode(',', $keys),
                $offset,
                $this->type . '.delete-items'
            ));
        }
    }

    public function save(CacheItemInterface $item): ?bool
    {
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            return $this->pool->save($item);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $item->getKey(),
                $offset,
                $this->type . '.save'
            ));
        }
    }

    public function saveDeferred(CacheItemInterface $item): ?bool
    {
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            return $this->pool->saveDeferred($item);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                $item->getKey(),
                $offset,
                $this->type . '.save-deferred'
            ));
        }
    }

    public function commit(): ?bool
    {
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            return $this->pool->commit();
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                'commit',
                $offset,
                $this->type . '.commit'
            ));
        }
    }
}
