<?php

declare(strict_types=1);

namespace Surfnet\ServiceProviderDashboard\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Drops the `production_entities_enabled` column from the `service` entity
 */
final class Version20240514071702 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Drops the `production_entities_enabled` column from the `service` entity';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service DROP production_entities_enabled');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE service ADD production_entities_enabled TINYINT(1) NOT NULL');
    }
}
