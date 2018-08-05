<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionRequestEnricher;

use Psr\Http\Message\RequestInterface;
use TechDeCo\ElasticApmAgent\Convenience\Middleware\OpenTransactionRequestEnricher;
use TechDeCo\ElasticApmAgent\Convenience\OpenTransaction;
use TechDeCo\ElasticApmAgent\Message\Request;
use function array_map;
use function implode;
use function in_array;
use function strtolower;

final class RequestHeaderBlacklistEnricher implements OpenTransactionRequestEnricher
{
    /**
     * @var string[]
     */
    private $headerBlacklist;

    public function __construct(string ...$headerBlacklist)
    {
        $this->headerBlacklist = array_map('strtolower', $headerBlacklist);
    }

    public function enrichFromRequest(OpenTransaction $transaction, RequestInterface $request): void
    {
        $apmRequest = $this->addHeadersToApmRequest(
            $request,
            $transaction->getContext()->getRequest() ?? Request::fromHttpRequest($request)
        );
        $transaction->setContext(
            $transaction->getContext()->withRequest($apmRequest)
        );
    }

    private function addHeadersToApmRequest(RequestInterface $httpRequest, Request $apmRequest): Request
    {
        foreach ($httpRequest->getHeaders() as $name => $valueList) {
            if (in_array(strtolower($name), $this->headerBlacklist, true)) {
                continue;
            }

            $apmRequest = $apmRequest->withHeader($name, implode(',', $valueList));
        }

        return $apmRequest;
    }
}
