<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Convenience\Middleware\OpenTransactionRequestEnricher;

use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionRequestEnricher\RequestHeaderBlacklistEnricher;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Request;
use TechDeCo\ElasticApmAgent\Message\Timestamp;

final class RequestHeaderBlacklistEnricherTest extends TestCase
{
    /**
     * @var OpenTransaction
     */
    private $transaction;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var RequestHeaderBlacklistEnricher
     */
    private $enricher;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->transaction = new OpenTransaction(
            Uuid::uuid4(),
            'test',
            new Timestamp(),
            'test'
        );
        $this->request     = new ServerRequest('GET', 'http://gaia.prime');
        $this->enricher    = new RequestHeaderBlacklistEnricher('not', 'also-not');
    }

    public function testDoesNotAddBlacklistedHeadersToRequest(): void
    {
        $request = $this->request->withHeader('foo', 'bar')
                                 ->withHeader('bla', 'bloo')
                                 ->withHeader('not', 'this')
                                 ->withHeader('also-not', 'this-one');

        $this->enricher->enrichFromRequest($this->transaction, $request);
        $json = $this->transaction
            ->getContext()
            ->getRequest()
            ->jsonSerialize();

        self::assertSame('bar', $json['headers']['foo']);
        self::assertSame('bloo', $json['headers']['bla']);
        self::assertArrayNotHasKey('not', $json['headers']);
        self::assertArrayNotHasKey('also-not', $json['headers']);
    }

    public function testWhitelistIsCaseInsensitive(): void
    {
        $request = $this->request->withHeader('NOT', 'this');

        $this->enricher->enrichFromRequest($this->transaction, $request);
        $json = $this->transaction
            ->getContext()
            ->getRequest()
            ->jsonSerialize();

        self::assertArrayNotHasKey('not', $json['headers']);
    }

    public function testImplodesMultipleValues(): void
    {
        $request = $this->request->withHeader('foo', ['bar', 'bla']);

        $this->enricher->enrichFromRequest($this->transaction, $request);
        $json = $this->transaction
            ->getContext()
            ->getRequest()
            ->jsonSerialize();

        self::assertSame('bar,bla', $json['headers']['foo']);
    }

    public function testUsesExistingApmRequest(): void
    {
        $apmRequest = Request::fromHttpRequest($this->request)
                             ->withBody('body');
        $context    = $this->transaction->getContext()
                                        ->withRequest($apmRequest);
        $this->transaction->setContext($context);

        $this->enricher->enrichFromRequest($this->transaction, $this->request);
        $json = $this->transaction
            ->getContext()
            ->getRequest()
            ->jsonSerialize();

        self::assertSame('body', $json['body']);
    }
}
