<?php

declare(strict_types=1);

namespace Surfnet\ServiceProviderDashboard\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20241031120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Restores the `production_entities_enabled` column to the `service` table if got removed by retracted migration Version20240514071702';
    }

    public function up(Schema $schema): void
    {
        // Check if the column exists
        $columns = $this->connection->createSchemaManager()->listTableColumns('service');
        $columnExists = false;
        foreach ($columns as $column) {
            if ($column->getName() === 'production_entities_enabled') {
                $columnExists = true;
                break;
            }
        }

        if (!$columnExists) {
            $this->addSql('ALTER TABLE service ADD production_entities_enabled TINYINT(1) NOT NULL DEFAULT 0');
        }
    }
}