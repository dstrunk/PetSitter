<?php

namespace App\Test\Utils\Controller\Trait;

use Tempest\Database\Connection\Connection;

trait RefreshesDatabase
{
    protected Connection $connection;
    protected bool $databaseTransactionsStarted = false;

    protected function registerRefreshesDatabase(): void
    {
        if (method_exists(get_parent_class($this), 'addSetupCallback')) {
            $this->addSetupCallback(fn () => $this->beginDatabaseTransaction());
        }

        if (method_exists(get_parent_class($this), 'addTearDownCallback')) {
            $this->addTearDownCallback(fn () => $this->rollbackDatabaseTransaction());
        }
    }

    public function beginDatabaseTransaction(): void
    {
        $this->databaseTransactionsStarted = true;

        $conn = $this->getDatabaseConnection();
        $conn->beginTransaction();
    }

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
