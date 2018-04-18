<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Dummy;

use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\TransactionMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Span;
use function usleep;

final class DummyHandler implements RequestHandlerInterface
{
    public const EXCEPTION_MESSAGE = 'test';
    public const EXCEPTION_CODE    = 255;
    public const MARK_GROUP        = 'group1';
    public const MARK_NAME         = 'mark1';
    public const MARK_VALUE        = 3.45;
    public const SPAN_NAME         = 'postgres';

    /**
     * @var int Time that the middleware sleeps in milliseconds
     */
    private $sleep = 1;

    /**
     * @var bool
     */
    private $throwsException;

    /**
     * @var ServerRequestInterface
     */
    public $request;

    public function __construct(int $sleep, ?bool $throwsException = false)
    {
        $this->sleep           = $sleep;
        $this->throwsException = $throwsException;
    }

    /**
     * @throws Exception
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $this->request = $request;

        /** @var OpenTransaction $transaction */
        $transaction = $request->getAttribute(TransactionMiddleware::TRANSACTION_ATTRIBUTE);
        $transaction->addMark(self::MARK_GROUP, self::MARK_NAME, self::MARK_VALUE);
        $transaction->addSpan(new Span(4.5, self::SPAN_NAME, 0.0, 'db'));

        if ($this->throwsException) {
            throw new Exception(self::EXCEPTION_MESSAGE, self::EXCEPTION_CODE);
        }

        usleep($this->sleep * 1000);

        return new Response();
    }
}
