<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Dummy;

use GuzzleHttp\Psr7\Response;
use Http\Client\Exception;
use Http\Client\Exception\NetworkException;
use Http\Client\HttpClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use function usleep;

final class DummyHttpClient implements HttpClient
{
    /**
     * @var int
     */
    private $busyTime;

    /**
     * @var bool
     */
    private $throwsException;

    /**
     * @var RequestInterface
     */
    public $request;

    public function __construct(int $busyTime, ?bool $throwsException = false)
    {
        $this->busyTime        = $busyTime;
        $this->throwsException = $throwsException;
    }

    /**
     * @throws Exception
     */
    public function sendRequest(RequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        if ($this->throwsException) {
            throw new NetworkException('foo', $request);
        }

        usleep($this->busyTime * 1000);

        return new Response();
    }
}
