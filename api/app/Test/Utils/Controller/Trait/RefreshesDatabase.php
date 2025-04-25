<?php

namespace App\Test\Utils\Controller\Trait;

use App\Test\Utils\Controller\Attribute\RunDuring;
use Tempest\Database\Connection\Connection;

trait RefreshesDatabase
{
    protected Connection $connection;
    protected bool $databaseTransactionsStarted = false;

    #[RunDuring('setUp')]
    public function beginDatabaseTransaction(): void
    {
        $this->databaseTransactionsStarted = true;

        $conn = $this->getDatabaseConnection();
        $conn->beginTransaction();
    }

    #[RunDuring('tearDown')]
    public function rollbackDatabaseTransaction(): void
    {
        if (!$this->databaseTransactionsStarted) {
            return;
        }

        $connection = $this->getDatabaseConnection();
        $connection->rollBack();

        $this->databaseTransactionsStarted = false;
    }

    public function getDatabaseConnection(): Connection
    {
        return $this->connection ??= $this->createDatabaseConnection();
    }

    public function createDatabaseConnection(): Connection
    {
        return $this->container->get(Connection::class);
    }
}
