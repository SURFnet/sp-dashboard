<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171219094607 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity ADD organization_name_en LONGTEXT DEFAULT NULL, ADD organization_display_name_en LONGTEXT DEFAULT NULL, ADD organization_url_en LONGTEXT DEFAULT NULL, ADD organization_name_nl LONGTEXT DEFAULT NULL, ADD organization_display_name_nl LONGTEXT DEFAULT NULL, ADD organization_url_nl LONGTEXT DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity DROP organization_name_en, DROP organization_display_name_en, DROP organization_url_en, DROP organization_name_nl, DROP organization_display_name_nl, DROP organization_url_nl');
    }
}
