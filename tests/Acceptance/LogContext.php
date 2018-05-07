<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use Behat\Behat\Context\Context;
use Gamez\Psr\Log\Log;
use Gamez\Psr\Log\TestLogger;
use PHPUnit\Framework\Assert;

final class LogContext implements Context
{
    /**
     * @var TestLogger
     */
    private $logger;

    public function __construct(TestLogger $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @BeforeScenario
     */
    public function cleanLog(): void
    {
        $this->logger->log = new Log();
    }

    /**
     * @Then successfully sending an asynchronous request has been logged
     */
    public function successfullySendingAnAsynchronousRequestHasBeenLogged(): void
    {
        Assert::assertTrue($this->logger->log->hasRecordsWithPartialMessage('Successful response on request'));
    }

    /**
     * @Then a failure while sending an asynchronous request has been logged
     */
    public function aFailureWhileSendingAnAsynchronousRequestHasBeenLogged(): void
    {
        Assert::assertTrue($this->logger->log->hasRecordsWithPartialMessage('Encountered error in response for request'));
    }
}
