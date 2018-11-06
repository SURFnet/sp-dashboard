<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add required service status columns for better status tracking
 */
class Version20181106121253 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'ALTER TABLE service 
            ADD service_type VARCHAR(50) NOT NULL, 
            ADD intake_status VARCHAR(50) NOT NULL, 
            ADD contract_signed VARCHAR(50) DEFAULT NULL, 
            ADD surfconext_representative_approved VARCHAR(50) DEFAULT NULL, 
            ADD privacy_questions_answered VARCHAR(50) DEFAULT NULL, 
            ADD connection_status VARCHAR(50) NOT NULL'
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf(
            $this->connection->getDatabasePlatform()->getName() !== 'mysql',
            'Migration can only be executed safely on \'mysql\'.'
        );

        $this->addSql(
            'ALTER TABLE service 
            DROP service_type, 
            DROP intake_status, 
            DROP contract_signed, 
            DROP surfconext_representative_approved, 
            DROP privacy_questions_answered, 
            DROP connection_status'
        );
    }
}
