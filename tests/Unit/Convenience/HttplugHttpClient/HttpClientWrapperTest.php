<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Convenience\HttplugHttpClient;

use GuzzleHttp\Psr7\Request;
use Http\Client\Exception;
use Http\Client\HttpClient;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use TechDeCo\ElasticApmAgent\Convenience\HttplugHttpClient\HttpClientWrapper;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Timestamp;
use TechDeCo\ElasticApmAgent\Tests\Dummy\DummyHttpClient;

final class HttpClientWrapperTest extends TestCase
{
    /**
     * @var UuidInterface
     */
    private $correlationId;

    /**
     * @var OpenTransaction
     */
    private $transaction;

    /**
     * @var DummyHttpClient
     */
    private $client;

    /**
     * @var RequestInterface
     */
    private $request;

    /**
     * @var HttpClient
     */
    private $wrapper;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->correlationId = Uuid::uuid4();
        $this->transaction   = new OpenTransaction(
            Uuid::uuid4(),
            'test',
            new Timestamp(),
            'request',
            $this->correlationId
        );
        $this->client        = new DummyHttpClient(5);
        $this->request       = new Request('GET', 'http://foo.bar');
        $this->wrapper       = new HttpClientWrapper($this->client);
        $this->wrapper->setOpenTransaction($this->transaction);
    }

    public function testReturnsResponse(): void
    {
        self::assertInstanceOf(ResponseInterface::class, $this->wrapper->sendRequest($this->request));
    }

    public function testAddsSpanWithNormalResponse(): void
    {
        $data = $this->transaction->toTransaction()->jsonSerialize();
        self::assertEmpty($data['spans']);

        $this->wrapper->sendRequest($this->request);

        $data = $this->transaction->toTransaction()->jsonSerialize();
        self::assertNotEmpty($data['spans']);
    }

    public function testAddsSpanWithException(): void
    {
        $this->expectException(Exception::class);

        $data = $this->transaction->toTransaction()->jsonSerialize();
        self::assertEmpty($data['spans']);

        $wrapper = new HttpClientWrapper(new DummyHttpClient(0, true));
        $wrapper->setOpenTransaction($this->transaction);
        $wrapper->sendRequest($this->request);

        $data = $this->transaction->toTransaction()->jsonSerialize();
        self::assertNotEmpty($data['spans']);
    }

    public function testSpanHasDuration(): void
    {
        $this->wrapper->sendRequest($this->request);

        $data = $this->transaction->toTransaction()->jsonSerialize();
        self::assertGreaterThan(5, $data['spans'][0]['duration']);
        self::assertLessThan(15, $data['spans'][0]['duration']);
    }

    public function testSpanHasOffset(): void
    {
        $this->wrapper->sendRequest($this->request);

        $data = $this->transaction->toTransaction()->jsonSerialize();
        self::assertGreaterThan(0, $data['spans'][0]['start']);
    }

    public function testAddsCorrelationHeader(): void
    {
        $this->wrapper->sendRequest($this->request);

        self::assertSame(
            $this->correlationId->toString(),
            $this->client->request->getHeaderLine(HttpClientWrapper::CORRELATION_ID_HEADER)
        );
    }
}
