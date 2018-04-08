<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use Behat\Behat\Context\Context;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use PHPUnit\Framework\Assert;
use TechDeCo\ElasticApmAgent\AsyncClient;
use TechDeCo\ElasticApmAgent\Exception\ClientException;

final class AsyncRequestContext implements Context
{
    /**
     * @var AsyncClient
     */
    private $client;

    /**
     * @var ClientException
     */
    private $exception;

    /**
     * @var TransactionRequestProvider
     */
    private $transactionRequestProvider;

    public function __construct(AsyncClient $client)
    {
        $this->client = $client;
    }

    /**
     * @BeforeScenario
     */
    public function injectRequestProvider(BeforeScenarioScope $scope): void
    {
        $this->transactionRequestProvider = $scope
            ->getEnvironment()
            ->getContext('TechDeCo\ElasticApmAgent\Tests\Acceptance\TransactionContext');
    }

    /**
     * @When I send the transactions asynchronously
     */
    public function iSendTheTransactionsAsynchronously(): void
    {
        try {
            $this->client->sendTransactionAsync($this->transactionRequestProvider->createRequest());
        } catch (ClientException $e) {
            $this->exception = $e;
        }
    }

    /**
     * @Then all asynchronously sent transactions are accepted
     */
    public function allAsynchronouslySentTransactionsAreAccepted(): void
    {
        $this->client->waitForResponses();
    }

    /**
     * @Then an asynchronously sent transaction fails
     */
    public function anAsynchronouslySentTransactionFails(): void
    {
        try {
            $this->client->waitForResponses();
        } catch (ClientException $e) {
            $this->exception = $e;
        }

        Assert::assertNotEmpty($this->exception);
    }
}
