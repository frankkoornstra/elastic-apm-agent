<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Client;

use Http\Client\HttpAsyncClient;
use Http\Discovery\Exception as DiscoveryException;
use Http\Discovery\HttpAsyncClientDiscovery;
use Http\Discovery\MessageFactoryDiscovery;
use Http\Message\MessageFactory;
use Psr\Http\Message\RequestInterface;
use Psr\Log\LoggerInterface;
use TechDeCo\ElasticApmAgent\Client;
use TechDeCo\ElasticApmAgent\ClientConfiguration;
use TechDeCo\ElasticApmAgent\Exception\ClientException;
use TechDeCo\ElasticApmAgent\Request\Error;
use TechDeCo\ElasticApmAgent\Request\Transaction;
use Throwable;
use function count;
use function json_encode;
use function register_shutdown_function;

final class HttplugAsyncClient implements Client
{
    /**
     * @var ClientConfiguration
     */
    private $config;

    /**
     * @var HttpAsyncClient
     */
    private $httpClient;

    /**
     * @var MessageFactory
     */
    private $httpMessageFactory;

    /**
     * @var PromiseCollection
     */
    private $promises;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @throws DiscoveryException
     */
    public function __construct(
        LoggerInterface $logger,
        ClientConfiguration $config,
        ?HttpAsyncClient $httpClient,
        ?MessageFactory $httpMessageFactory
    ) {
        $this->logger             = $logger;
        $this->config             = $config;
        $this->httpClient         = $httpClient ?? HttpAsyncClientDiscovery::find();
        $this->httpMessageFactory = $httpMessageFactory ?? MessageFactoryDiscovery::find();
        $this->promises           = new PromiseCollection($logger);

        register_shutdown_function([$this, 'waitForResponses']);
    }

    /**
     * @throws ClientException
     */
    public function sendTransaction(Transaction $transaction): void
    {
        $request = $this->httpMessageFactory->createRequest(
            'POST',
            $this->config->getTransactionsEndpoint(),
            [],
            json_encode($transaction)
        );

        $this->sendRequest($request);
    }

    /**
     * @throws ClientException
     */
    public function sendError(Error $error): void
    {
        $request = $this->httpMessageFactory->createRequest(
            'POST',
            $this->config->getErrorsEndpoint(),
            [],
            json_encode($error)
        );

        $this->sendRequest($request);
    }

    /**
     * @throws ClientException
     */
    private function sendRequest(RequestInterface $request): void
    {
        $requestCount = count($this->promises) + 1;

        try {
            $request = $request->withHeader('Content-Type', 'application/json');
            if ($this->config->needsAuthentication()) {
                $this->logger->debug('Adding authentication token to request');
                $request = $request->withHeader('Authorization', 'Bearer ' . $this->config->getToken());
            }

            $this->logger->debug('Sending asynchronous request #' . $requestCount);
            $this->promises->add($this->httpClient->sendAsyncRequest($request));
        } catch (Throwable $e) {
            $this->logger->error('Encountered error while sending asynchronous request #' . $requestCount, [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);
            throw new ClientException('Could not send request due to configuration error', 0, $e);
        }
    }

    /**
     * @throws ClientException
     */
    public function waitForResponses(): void
    {
        $exceptionList = $this->promises->resolveAll();

        if (! empty($exceptionList)) {
            throw ClientException::fromException('Encountered errors while resolving requests', ...$exceptionList);
        }
    }
}
