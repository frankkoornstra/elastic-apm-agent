<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Convenience\Middleware\OpenTransactionRequestEnricher;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionRequestEnricher\ResponseHeaderBlacklistEnricher;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Response as ApmResponse;
use TechDeCo\ElasticApmAgent\Message\Timestamp;

final class ResponseHeaderBlacklistEnricherTest extends TestCase
{
    /**
     * @var OpenTransaction
     */
    private $transaction;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var ResponseHeaderBlacklistEnricher
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
        $this->response    = new Response();
        $this->enricher    = new ResponseHeaderBlacklistEnricher('not', 'also-not');
    }

    public function testDoesNotAddBlacklistedHeadersToRequest(): void
    {
        $response = $this->response->withHeader('foo', 'bar')
                                   ->withHeader('bla', 'bloo')
                                   ->withHeader('not', 'this')
                                   ->withHeader('also-not', 'this-one');

        $this->enricher->enrichFromResponse($this->transaction, $response);
        $json = $this->transaction
            ->getContext()
            ->getResponse()
            ->jsonSerialize();

        self::assertSame('bar', $json['headers']['foo']);
        self::assertSame('bloo', $json['headers']['bla']);
        self::assertArrayNotHasKey('not', $json['headers']);
        self::assertArrayNotHasKey('also-not', $json['headers']);
    }

    public function testBlacklistIsCaseInsensitive(): void
    {
        $response = $this->response->withHeader('NOT', 'this')
                                   ->withHeader('foo', 'bar');

        $this->enricher->enrichFromResponse($this->transaction, $response);
        $json = $this->transaction
            ->getContext()
            ->getResponse()
            ->jsonSerialize();

        self::assertArrayNotHasKey('not', $json['headers']);
    }

    public function testImplodesMultipleValues(): void
    {
        $response = $this->response->withHeader('foo', ['bar', 'bla']);

        $this->enricher->enrichFromResponse($this->transaction, $response);
        $json = $this->transaction
            ->getContext()
            ->getResponse()
            ->jsonSerialize();

        self::assertSame('bar,bla', $json['headers']['foo']);
    }

    public function testUsesExistingApmResponse(): void
    {
        $apmResponse = ApmResponse::fromHttpResponse($this->response)
                                  ->resultingInStatusCode(503);
        $context     = $this->transaction->getContext()
                                         ->withResponse($apmResponse);
        $this->transaction->setContext($context);

        $this->enricher->enrichFromResponse($this->transaction, $this->response);
        $json = $this->transaction
            ->getContext()
            ->getResponse()
            ->jsonSerialize();

        self::assertSame(503, $json['status_code']);
    }
}
