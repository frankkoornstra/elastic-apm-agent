<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionRequestEnricher;

use Psr\Http\Message\ResponseInterface;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionResponseEnricher;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Response;
use function array_map;
use function implode;
use function in_array;
use function strtolower;

final class ResponseHeaderBlacklistEnricher implements OpenTransactionResponseEnricher
{
    /**
     * @var string[]
     */
    private $headerBlacklist;

    public function __construct(string ...$headerBlacklist)
    {
        $this->headerBlacklist = array_map('strtolower', $headerBlacklist);
    }

    public function enrichFromResponse(OpenTransaction $transaction, ResponseInterface $response): void
    {
        $apmResponse = $this->addHeadersToApmResponse(
            $response,
            $transaction->getContext()->getResponse() ?? Response::fromHttpResponse($response)
        );
        $transaction->setContext(
            $transaction->getContext()->withResponse($apmResponse)
        );
    }

    private function addHeadersToApmResponse(ResponseInterface $httpResponse, Response $apmResponse): Response
    {
        foreach ($httpResponse->getHeaders() as $name => $valueList) {
            if (in_array(strtolower($name), $this->headerBlacklist, true)) {
                continue;
            }

            $apmResponse = $apmResponse->withHeader($name, implode(',', $valueList));
        }

        return $apmResponse;
    }
}
