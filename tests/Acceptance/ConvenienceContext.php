<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Cache\CacheItemPoolInterface;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransactionEnricher;
use TechDeCo\ElasticApmAgent\Message\Timestamp;

final class ConvenienceContext implements Context
{
    /**
     * @var CacheItemPoolInterface|OpenTransactionEnricher
     */
    private $cache;

    /**
     * @var OpenTransaction|null
     */
    private $open;

    /**
     * @var mixed[]
     */
    private $closed;

    public function __construct(CacheItemPoolInterface $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @Given a default open transaction
     */
    public function aDefaultOpenTransaction(): void
    {
        $this->open = new OpenTransaction(
            Uuid::uuid4(),
            'alloy',
            new Timestamp(),
            'nora'
        );

        $this->cache->setOpenTransaction($this->open);
    }

    /**
     * @Given I get item :key from cache
     */
    public function iGetItemFromCache(string $key): void
    {
        $this->cache->getItem($key);
    }

    /**
     * @When I close the open transaction
     */
    public function iCloseTheOpenTransaction(): void
    {
        $this->closed = $this->open->toTransaction()->jsonSerialize();
    }

    /**
     * @Then the closed transaction has a cache span for getting item :key
     */
    public function theClosedTransactionHasACacheSpanForGettingItem(string $key): void
    {
        foreach ($this->closed['spans'] as $span) {
            try {
                Assert::assertSame($key, $span['name']);
                Assert::assertStringEndsWith('.get-item', $span['type']);

                return;
            } catch (ExpectationFailedException $e) {
            }
        }

        Assert::fail('No matching span found');
    }
}
