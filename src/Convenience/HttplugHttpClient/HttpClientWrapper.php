<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\HttplugHttpClient;

use Exception;
use Http\Client\Exception as ClientException;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransactionEnricher;
use TechDeCo\ElasticApmAgent\Convenience\Util\Stopwatch;
use TechDeCo\ElasticApmAgent\Message\Span;
use function sprintf;

final class HttpClientWrapper implements HttpClient, OpenTransactionEnricher
{
    public const CORRELATION_ID_HEADER = 'X-Correlation-ID';

    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @var OpenTransaction
     */
    private $transaction;

    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    public function setOpenTransaction(OpenTransaction $transaction): void
    {
        $this->transaction = $transaction;
    }

    /**
     * @throws ClientException
     * @throws Exception
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $start  = Stopwatch::start();
        $offset = $this->transaction->getStartOffset();

        try {
            $request = $request->withAddedHeader(
                self::CORRELATION_ID_HEADER,
                $this->transaction->getCorrelationId()->toString()
            );

            return $this->client->sendRequest($request);
        } finally {
            $this->transaction->addSpan(new Span(
                Stopwatch::stop($start),
                sprintf('%s %s', $request->getMethod(), $request->getUri()->__toString()),
                $offset,
                'http.request'
            ));
        }
    }
}
