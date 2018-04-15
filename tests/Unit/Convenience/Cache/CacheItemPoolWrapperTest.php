<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Convenience\Cache;

use Cache\Adapter\Common\CacheItem;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\Convenience\Cache\CacheItemPoolWrapper;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Timestamp;
use TechDeCo\ElasticApmAgent\Tests\Dummy\DummyCacheItemPool;

final class CacheItemPoolWrapperTest extends TestCase
{
    /**
     * @var CacheItemPoolInterface
     */
    private $exceptionWrapper;

    /**
     * @var CacheItemPoolInterface
     */
    private $busyWrapper;

    /**
     * @var CacheItemPoolInterface
     */
    private $directWrapper;

    /**
     * @var CacheItemInterface
     */
    private $item;

    /**
     * @var OpenTransaction
     */
    private $transaction;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->transaction = new OpenTransaction(
            Uuid::uuid4(),
            'test',
            new Timestamp(),
            'request'
        );
        $this->item        = new CacheItem('key', true, 'value');
        $this->busyWrapper = new CacheItemPoolWrapper(new DummyCacheItemPool($this->item, 5), 'dummy');
        $this->busyWrapper->setOpenTransaction($this->transaction);
        $this->exceptionWrapper = new CacheItemPoolWrapper(new DummyCacheItemPool($this->item, 5, true), 'dummy');
        $this->exceptionWrapper->setOpenTransaction($this->transaction);
        $this->directWrapper = new CacheItemPoolWrapper(new DummyCacheItemPool($this->item, 0), 'dummy');
        $this->directWrapper->setOpenTransaction($this->transaction);
    }

    private function assertBusyTime(): void
    {
        $data     = $this->transaction->toTransaction()->jsonSerialize();
        $start    = $data['spans'][0]['start'];
        $duration = $data['spans'][0]['duration'];

        Assert::assertGreaterThan(0, $start);
        Assert::assertGreaterThan(5, $duration);
        Assert::assertLessThan(15, $duration);
    }

    public function testGetItemReturn(): void
    {
        self::assertSame($this->item, $this->directWrapper->getItem('foo'));
    }

    public function testGetItemRecordsBusyTime(): void
    {
        $this->busyWrapper->getItem('foo');

        $this->assertBusyTime();
    }

    public function testGetItemRecordsBusyTimeWhenExceptionIsThrown(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->exceptionWrapper->getItems(['foo']);

        $this->assertBusyTime();
    }

    public function testGetItemsReturn(): void
    {
        Assert::assertSame($this->item, $this->directWrapper->getItems(['foo'])[0]);
    }

    public function testGetItemsRecordsBusyTime(): void
    {
        $this->busyWrapper->getItems(['foo']);

        $this->assertBusyTime();
    }

    public function testGetItemsRecordsBusyTimeWhenExceptionIsThrown(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->exceptionWrapper->getItems(['foo']);

        $this->assertBusyTime();
    }

    public function testHasItemReturn(): void
    {
        Assert::assertTrue($this->directWrapper->hasItem('foo'));
    }

    public function testHasItemRecordsBusyTime(): void
    {
        $this->busyWrapper->hasItem('foo');

        $this->assertBusyTime();
    }

    public function testHasItemRecordsBusyTimeWhenExceptionIsThrown(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->exceptionWrapper->hasItem('foo');

        $this->assertBusyTime();
    }

    public function testClearReturn(): void
    {
        Assert::assertTrue($this->directWrapper->clear());
    }

    public function testClearRecordsBusyTime(): void
    {
        $this->busyWrapper->clear();

        $this->assertBusyTime();
    }

    public function testDeleteItemReturn(): void
    {
        Assert::assertTrue($this->directWrapper->deleteItem('foo'));
    }

    public function testDeleteItemRecordsBusyTime(): void
    {
        $this->busyWrapper->deleteItem('foo');

        $this->assertBusyTime();
    }

    public function testDeleteItemRecordsBusyTimeWhenExceptionIsThrown(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->exceptionWrapper->deleteItem('foo');

        $this->assertBusyTime();
    }

    public function testDeleteItemsReturn(): void
    {
        Assert::assertTrue($this->directWrapper->deleteItems(['foo']));
    }

    public function testDeleteItemsRecordsBusyTime(): void
    {
        $this->busyWrapper->deleteItems(['foo']);

        $this->assertBusyTime();
    }

    public function testDeleteItemsRecordsBusyTimeWhenExceptionIsThrown(): void
    {
        $this->expectException(InvalidArgumentException::class);

        $this->exceptionWrapper->deleteItems(['foo']);

        $this->assertBusyTime();
    }

    public function testSaveReturn(): void
    {
        Assert::assertTrue($this->directWrapper->save($this->item));
    }

    public function testSaveRecordsBusyTime(): void
    {
        $this->busyWrapper->save(new CacheItem('foo'));

        $this->assertBusyTime();
    }

    public function testSaveDeferredReturn(): void
    {
        Assert::assertTrue($this->directWrapper->saveDeferred($this->item));
    }

    public function testSaveDeferredRecordsBusyTime(): void
    {
        $this->busyWrapper->saveDeferred(new CacheItem('foo'));

        $this->assertBusyTime();
    }

    public function testCommitReturn(): void
    {
        Assert::assertTrue($this->directWrapper->commit());
    }

    public function testCommitRecordsBusyTime(): void
    {
        $this->busyWrapper->commit();

        $this->assertBusyTime();
    }
}
