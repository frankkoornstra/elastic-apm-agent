<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use Behat\Behat\Context\Context;
use GuzzleHttp\Psr7\Request;
use Http\Client\HttpClient;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\AssertionFailedError;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Cache\CacheItemPoolInterface;
use Ramsey\Uuid\Uuid;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
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
     * @var HttpClient|OpenTransactionEnricher
     */
    private $httpClient;

    /**
     * @var OpenTransaction|null
     */
    private $open;

    /**
     * @var mixed[]
     */
    private $closed;

    public function __construct(CacheItemPoolInterface $cache, HttpClient $httpClient)
    {
        $this->cache      = $cache;
        $this->httpClient = $httpClient;
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
        $this->httpClient->setOpenTransaction($this->open);
    }

    /**
     * @When I close the open transaction
     */
    public function iCloseTheOpenTransaction(): void
    {
        $this->closed = $this->open->toTransaction()->jsonSerialize();
    }

    /**
     * @throws AssertionFailedError
     * @throws InvalidArgumentException
     */
    private function assertSpanExists(string $key, string $type): void
    {
        foreach ($this->closed['spans'] as $span) {
            try {
                Assert::assertSame($key, $span['name']);
                Assert::assertSame($type, $span['type']);

                return;
            } catch (ExpectationFailedException $e) {
            }
        }

        Assert::fail('No matching span exists');
    }

    /**
     * @Given I get item :key from cache
     */
    public function iGetItemFromCache(string $key): void
    {
        $this->cache->getItem($key);
    }

    /**
     * @Then the closed transaction has a cache span for getting item :key
     */
    public function theClosedTransactionHasACacheSpanForGettingItem(string $key): void
    {
        $this->assertSpanExists($key, 'dummy.get-item');
    }

    /**
     * @Then the closed transaction has an http request span for :url
     */
    public function theClosedTransactionHasAnHttpRequestSpanFor(string $url): void
    {
        $this->assertSpanExists('GET ' . $url, 'http.request');
    }

    /**
     * @Given I send an http request for :url
     */
    public function iSendAnHttpRequestFor(string $url): void
    {
        $request = new Request('GET', $url);
        $this->httpClient->sendRequest($request);
    }
}
