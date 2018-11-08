<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Add required service status columns for better status tracking.
 * And fill the existing records with sensible default values.
 *
 * The following default are set for:
 *
 *  | Column           | Value          | Matching Service entity constant
 *  |==================|================|====================================
 *  | service_type     | non-institute  | Service::SERVICE_TYPE_NON_INSTITUTE
 *  | intake_status    | yes            | Service::INTAKE_STATUS_YES
 *  | contract_signed  | yes            | Service::CONTRACT_SIGNED_YES
 */
class Version20181107134447 extends AbstractMigration
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
            ADD connection_status VARCHAR(50) NOT NULL'
        );

        $this->addSql(
            sprintf(
                'UPDATE service SET 
                service_type = "%s",
                intake_status = "%s",
                contract_signed = "%s"',
                'non-institute',
                'yes',
                'yes'
            )
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
            DROP connection_status'
        );
    }
}
