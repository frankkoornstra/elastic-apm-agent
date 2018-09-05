<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Exception;

use Psr\Http\Message\ResponseInterface;
use RuntimeException;
use Throwable;
use function count;
use function sprintf;

final class ClientException extends RuntimeException implements Exception
{
    /**
     * @var Throwable[]
     */
    private $exceptionList = [];

    public static function fromResponse(string $message, ResponseInterface $response): self
    {
        return new self(sprintf('%s: %s', $message, $response->getBody()->getContents()));
    }

    public static function fromException(string $message, Throwable ...$exception): self
    {
        if (count($exception) === 1) {
            return new self($message, 0, $exception[0] ?? null);
        }

        $me                = new self($message);
        $me->exceptionList = $exception;

        return $me;
    }

    /**
     * @return Throwable[]
     */
    public function getExceptionList(): array
    {
        return $this->exceptionList;
    }
}
