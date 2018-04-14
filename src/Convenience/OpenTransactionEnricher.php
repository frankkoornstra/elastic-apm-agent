<?php
declare(strict_types=1);

namespace TechDeCo\ElasticApmAgent\Convenience;

interface OpenTransactionEnricher
{
    public function setOpenTransaction(OpenTransaction $transaction): void;
}
