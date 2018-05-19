<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Client;

use Countable;
use Http\Promise\Promise;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use TechDeCo\ElasticApmAgent\Exception\ClientException;
use Throwable;
use function array_filter;
use function count;

final class PromiseCollection implements Countable
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Promise[]
     */
    private $promiseList = [];

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function add(Promise $promise): void
    {
        $this->promiseList[] = $promise;
    }

    public function count(): int
    {
        return count($this->promiseList);
    }

    /**
     * @return Throwable[]
     */
    public function resolveAll(): array
    {
        $exceptionList = [];
        foreach ($this->promiseList as $index => $promise) {
            $exceptionList[] = $this->resolve($index + 1, $promise);
        }

        $this->promiseList = [];

        return array_filter($exceptionList);
    }

    private function resolve(int $promiseCount, Promise $promise): ?Throwable
    {
        try {
            $this->logger->debug('Waiting for response on request #' . $promiseCount);
            $this->verifyResponse($promise->wait());
            $this->logger->debug('Successful response on request #' . $promiseCount);
        } catch (Throwable $e) {
            $this->logger->error('Encountered error in response for request #' . $promiseCount, [
                'exception' => $e,
                'message' => $e->getMessage(),
            ]);

            return $e;
        }

        return null;
    }

    /**
     * @throws ClientException
     */
    private function verifyResponse(ResponseInterface $response): void
    {
        $status = $response->getStatusCode();

        if ($status >= 400 && $status < 500) {
            throw ClientException::fromResponse('Bad request', $response);
        }
        if ($status >= 500) {
            throw ClientException::fromResponse('APM internal server error', $response);
        }
    }
}
