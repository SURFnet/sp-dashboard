<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20171026075111 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE privacy_questions (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, what_data LONGTEXT DEFAULT NULL, access_data LONGTEXT DEFAULT NULL, country LONGTEXT DEFAULT NULL, security_measures LONGTEXT DEFAULT NULL, certification TINYINT(1) DEFAULT NULL, certification_location VARCHAR(255) DEFAULT NULL, certification_valid_from DATE DEFAULT NULL, certification_valid_to DATE DEFAULT NULL, surfmarket_dpa_agreement TINYINT(1) DEFAULT NULL, surfnet_dpa_agreement TINYINT(1) DEFAULT NULL, sn_dpa_why_not LONGTEXT DEFAULT NULL, privacy_policy TINYINT(1) DEFAULT NULL, privacy_policy_url VARCHAR(255) DEFAULT NULL, other_info LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_FE704F20ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE privacy_questions ADD CONSTRAINT FK_FE704F20ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE privacy_questions');
    }
}
