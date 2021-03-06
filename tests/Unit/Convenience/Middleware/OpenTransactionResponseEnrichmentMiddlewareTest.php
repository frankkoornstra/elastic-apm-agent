<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Unit\Convenience\Middleware;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\ServerRequest;
use PHPUnit\Framework\Error\Warning;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Ramsey\Uuid\Uuid;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionResponseEnricher;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionResponseEnrichmentMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\TransactionMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Timestamp;

final class OpenTransactionResponseEnrichmentMiddlewareTest extends TestCase
{
    /**
     * @var OpenTransactionResponseEnricher|ObjectProphecy
     */
    private $enricher;

    /**
     * @var ServerRequestInterface
     */
    private $request;

    /**
     * @var ResponseInterface
     */
    private $response;

    /**
     * @var RequestHandlerInterface|ObjectProphecy
     */
    private $handler;

    /**
     * @var OpenTransaction
     */
    private $transaction;

    /**
     * @var OpenTransactionResponseEnrichmentMiddleware
     */
    private $middleware;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->enricher    = $this->prophesize(OpenTransactionResponseEnricher::class);
        $this->request     = new ServerRequest('GET', 'http://foo.bar');
        $this->response    = new Response();
        $this->handler     = $this->prophesize(RequestHandlerInterface::class);
        $this->transaction = new OpenTransaction(
            Uuid::uuid4(),
            'test',
            new Timestamp(),
            'request'
        );
        $this->middleware  = new OpenTransactionResponseEnrichmentMiddleware(
            $this->enricher->reveal()
        );

        $this->request = $this->request->withAttribute(
            TransactionMiddleware::TRANSACTION_ATTRIBUTE,
            $this->transaction
        );
        $this->handler->handle(Argument::any())
                      ->willReturn($this->response);
    }

    public function testForwardsToHandler(): void
    {
        $response = $this->middleware->process($this->request, $this->handler->reveal());

        self::assertSame($this->response, $response);
    }

    public function testEnrichesOpenTransaction(): void
    {
        $this->enricher->enrichFromResponse($this->transaction, $this->response)
                       ->shouldBeCalled();

        $this->middleware->process($this->request, $this->handler->reveal());
    }

    public function testAssertsTransactionInRequest(): void
    {
        $this->expectException(Warning::class);
        $this->expectExceptionMessageRegExp('#transaction#');

        $this->middleware->process(
            $this->request->withoutAttribute(TransactionMiddleware::TRANSACTION_ATTRIBUTE),
            $this->handler->reveal()
        );
    }
}
