<?php

namespace App\Test\Utils\Controller\Trait;

use PHPUnit\Framework\Attributes\AfterClass;
use PHPUnit\Framework\Attributes\BeforeClass;
use Tempest\Database\Connection\Connection;

trait RefreshesDatabase
{
    private Connection $connection;
    private bool $databaseTransactionsStarted = false;

    #[BeforeClass]
    public function beginDatabaseTransaction(): void
    {
        $this->databaseTransactionsStarted = true;

        $conn = $this->getDatabaseConnection();
        $conn->beginTransaction();
    }

    #[AfterClass]
    public function rollbackDatabaseTransaction(): void
    {
        if (!$this->databaseTransactionsStarted) {
            return;
        }

        $connection = $this->getDatabaseConnection();
        $connection->rollBack();

        $this->databaseTransactionsStarted = false;
    }

    private function getDatabaseConnection(): Connection
    {
        return $this->connection ??= $this->container->get(Connection::class);
    }
}
