<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Middleware;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Exception\InvalidUuidStringException;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use TechDeCo\ElasticApmAgent\Convenience\HttplugHttpClient\HttpClientWrapper;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Exception\ClientException;
use TechDeCo\ElasticApmAgent\Message\Timestamp;
use TechDeCo\ElasticApmAgent\Request\Transaction;
use function sprintf;

final class TransactionMiddleware extends Middleware
{
    public const TRANSACTION_ATTRIBUTE = 'apm-transaction';

    /**
     * @throws ClientException
     */
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $openTransaction = new OpenTransaction(
            Uuid::uuid4(),
            sprintf('%s %s', $request->getMethod(), $request->getUri()->__toString()),
            new Timestamp(),
            'request',
            $this->getCorrelationId($request)
        );

        try {
            $request = $request->withAttribute(self::TRANSACTION_ATTRIBUTE, $openTransaction);

            return $handler->handle($request);
        } finally {
            $transaction = (new Transaction($this->service, $openTransaction->toTransaction()))
                ->onSystem($this->system)
                ->inProcess($this->process);

            $this->client->sendTransaction($transaction);
        }
    }

    private function getCorrelationId(ServerRequestInterface $request): UuidInterface
    {
        try {
            return Uuid::fromString($request->getHeaderLine(HttpClientWrapper::CORRELATION_ID_HEADER));
        } catch (InvalidUuidStringException $e) {
            return Uuid::uuid4();
        }
    }
}
