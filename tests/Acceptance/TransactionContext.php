<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Tests\Acceptance;

use Behat\Behat\Context\Context;
use Ramsey\Uuid\UuidInterface;
use TechDeCo\ElasticApmAgent\Message\Service;
use TechDeCo\ElasticApmAgent\Message\Timestamp;
use TechDeCo\ElasticApmAgent\Message\Transaction;
use TechDeCo\ElasticApmAgent\Message\VersionedName;
use TechDeCo\ElasticApmAgent\Request\Transaction as Request;

final class TransactionContext implements Context, TransactionRequestProvider
{
    /**
     * @var VersionedName
     */
    private $agent;

    /**
     * @var Service
     */
    private $service;

    /**
     * @var Transaction
     */
    private $transaction;

    /**
     * @Given agent :name with version :version
     */
    public function agentWithVersion(string $name, string $version): void
    {
        $this->agent = new VersionedName($name, $version);
    }

    /**
     * @Given the service :name
     */
    public function theService(string $name): void
    {
        $this->service = new Service($this->agent, $name);
    }

    /**
     * @Given a transaction with id :id and name :name and duration :duration and type :type that started at :timestamp
     */
    public function aTransactionWithIdAndNameAndDurationAndTypeThatStartedAt(
        UuidInterface $id,
        string $name,
        float $duration,
        string $type,
        Timestamp $timestamp
    ): void {
        $this->transaction = new Transaction($duration, $id, $name, $timestamp, $type);
    }

        /**
         * @Given an invalid transaction
         */
    public function anInvalidTransaction(): void
    {
        $this->transaction = new Transaction(0.0, new InvalidUuid(), 'invalid', new Timestamp(), 'invalid');
    }

    public function createRequest(): Request
    {
        return new Request($this->service, $this->transaction);
    }
}
