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
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionRequestEnricher;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionRequestEnrichmentMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\TransactionMiddleware;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Timestamp;

final class OpenTransactionRequestEnrichmentMiddlewareTest extends TestCase
{
    /**
     * @var OpenTransactionRequestEnricher|ObjectProphecy
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
     * @var OpenTransactionRequestEnrichmentMiddleware
     */
    private $middleware;

    /**
     * @before
     */
    public function setUpDependencies(): void
    {
        $this->enricher    = $this->prophesize(OpenTransactionRequestEnricher::class);
        $this->request     = new ServerRequest('GET', 'http://foo.bar');
        $this->response    = new Response();
        $this->handler     = $this->prophesize(RequestHandlerInterface::class);
        $this->transaction = new OpenTransaction(
            Uuid::uuid4(),
            'test',
            new Timestamp(),
            'request'
        );
        $this->middleware  = new OpenTransactionRequestEnrichmentMiddleware(
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
        $this->enricher->enrichFromRequest($this->transaction, $this->request)
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
