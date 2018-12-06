<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20181206094559 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity ADD client_secret VARCHAR(50) DEFAULT NULL, ADD redirect_uris LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json_array)\', ADD grant_type VARCHAR(50) DEFAULT NULL, ADD protocol VARCHAR(50) DEFAULT NULL, ADD enable_playground TINYINT(1) DEFAULT NULL');
    }

    /**
     * @param Schema $schema
     * @throws \Doctrine\DBAL\DBALException
     */
    public function postUp(Schema $schema)
    {
        $this->connection->executeUpdate("UPDATE entity SET protocol='saml20'");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity DROP client_secret, DROP redirect_uris, DROP grant_type, DROP protocol, DROP enable_playground');
    }
}
