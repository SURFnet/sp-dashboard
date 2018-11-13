<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Squashed migration that replaces all previous migrations.
 *
 * The problem with the previous migrations was that there was no initial migration
 * to base the other migrations on. This is fixed with this migration.
 *
 * This migration is the result of bin/console doctrine:schema:create --dump-sql
 * And will truncate the existing migrations that where registered in the migration_versions table.
 */
class Version20180101010000 extends AbstractMigration
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

        // If this migration is run on an existing DB, clear the version table and play the squash migration.
        if (count($this->version->getConfiguration()->getMigratedVersions()) > 0) {
            $this->addSql("TRUNCATE migration_versions");
        }

        $queries = <<<SQL
CREATE TABLE privacy_questions (id INT AUTO_INCREMENT NOT NULL, service_id INT NOT NULL, what_data LONGTEXT DEFAULT NULL, access_data LONGTEXT DEFAULT NULL, country LONGTEXT DEFAULT NULL, security_measures LONGTEXT DEFAULT NULL, certification TINYINT(1) DEFAULT NULL, certification_location VARCHAR(255) DEFAULT NULL, certification_valid_from DATE DEFAULT NULL, certification_valid_to DATE DEFAULT NULL, surfmarket_dpa_agreement TINYINT(1) DEFAULT NULL, surfnet_dpa_agreement TINYINT(1) DEFAULT NULL, sn_dpa_why_not LONGTEXT DEFAULT NULL, privacy_policy TINYINT(1) DEFAULT NULL, privacy_policy_url VARCHAR(255) DEFAULT NULL, other_info LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_FE704F20ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE entity (id CHAR(36) NOT NULL COMMENT '(DC2Type:guid)', service_id INT NOT NULL, archived TINYINT(1) NOT NULL, environment VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, ticket_no VARCHAR(255) DEFAULT NULL, manage_id VARCHAR(255) DEFAULT NULL, import_url VARCHAR(255) DEFAULT NULL, metadata_url VARCHAR(255) DEFAULT NULL, pasted_metadata LONGTEXT DEFAULT NULL, metadata_xml LONGTEXT DEFAULT NULL, name_id_format VARCHAR(255) DEFAULT NULL, acs_location VARCHAR(255) DEFAULT NULL, entity_id VARCHAR(255) DEFAULT NULL, certificate LONGTEXT DEFAULT NULL, logo_url VARCHAR(255) DEFAULT NULL, name_nl VARCHAR(255) DEFAULT NULL, name_en VARCHAR(255) DEFAULT NULL, description_nl LONGTEXT DEFAULT NULL, description_en LONGTEXT DEFAULT NULL, application_url VARCHAR(255) DEFAULT NULL, eula_url VARCHAR(255) DEFAULT NULL, administrative_contact LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', technical_contact LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', support_contact LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', given_name_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', sur_name_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', common_name_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', display_name_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', email_address_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', organization_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', organization_type_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', affiliation_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', entitlement_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', principle_name_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', uid_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', preferred_language_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', personal_code_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', scoped_affiliation_attribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', edu_person_targeted_idattribute LONGTEXT DEFAULT NULL COMMENT '(DC2Type:object)', organization_name_en LONGTEXT DEFAULT NULL, organization_display_name_en LONGTEXT DEFAULT NULL, organization_url_en LONGTEXT DEFAULT NULL, organization_name_nl LONGTEXT DEFAULT NULL, organization_display_name_nl LONGTEXT DEFAULT NULL, organization_url_nl LONGTEXT DEFAULT NULL, comments LONGTEXT DEFAULT NULL, UNIQUE INDEX UNIQ_E284468BF396750 (id), INDEX IDX_E284468ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, name_id VARCHAR(150) NOT NULL, display_name VARCHAR(255) NOT NULL, email_address VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE contact_service (contact_id INT NOT NULL, service_id INT NOT NULL, INDEX IDX_7BB2BB53E7A1254A (contact_id), INDEX IDX_7BB2BB53ED5CA9E6 (service_id), PRIMARY KEY(contact_id, service_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE service( id INT AUTO_INCREMENT NOT NULL, guid CHAR(36) DEFAULT NULL COMMENT '(DC2Type:guid)', name VARCHAR(255) NOT NULL, team_name VARCHAR(255) NOT NULL, production_entities_enabled TINYINT(1) NOT NULL, privacy_questions_enabled TINYINT(1) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE lexik_trans_unit_translations (id INT AUTO_INCREMENT NOT NULL, file_id INT DEFAULT NULL, trans_unit_id INT DEFAULT NULL, locale VARCHAR(10) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, modified_manually TINYINT(1) NOT NULL, INDEX IDX_B0AA394493CB796C (file_id), INDEX IDX_B0AA3944C3C583C9 (trans_unit_id), UNIQUE INDEX trans_unit_locale_idx (trans_unit_id, locale), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE lexik_trans_unit (id INT AUTO_INCREMENT NOT NULL, key_name VARCHAR(255) NOT NULL, domain VARCHAR(255) NOT NULL, created_at DATETIME DEFAULT NULL, updated_at DATETIME DEFAULT NULL, UNIQUE INDEX key_domain_idx (key_name, domain), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
CREATE TABLE lexik_translation_file (id INT AUTO_INCREMENT NOT NULL, domain VARCHAR(255) NOT NULL, locale VARCHAR(10) NOT NULL, extention VARCHAR(10) NOT NULL, path VARCHAR(255) NOT NULL, hash VARCHAR(255) NOT NULL, UNIQUE INDEX hash_idx (hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB;
ALTER TABLE privacy_questions ADD CONSTRAINT FK_FE704F20ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id);
ALTER TABLE entity ADD CONSTRAINT FK_E284468ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id);
ALTER TABLE contact_service ADD CONSTRAINT FK_7BB2BB53E7A1254A FOREIGN KEY (contact_id) REFERENCES contact (id) ON DELETE CASCADE;
ALTER TABLE contact_service ADD CONSTRAINT FK_7BB2BB53ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id) ON DELETE CASCADE;
ALTER TABLE lexik_trans_unit_translations ADD CONSTRAINT FK_B0AA394493CB796C FOREIGN KEY (file_id) REFERENCES lexik_translation_file (id);
ALTER TABLE lexik_trans_unit_translations ADD CONSTRAINT FK_B0AA3944C3C583C9 FOREIGN KEY (trans_unit_id) REFERENCES lexik_trans_unit (id);
SQL;
        $this->addSql($queries);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->abortIf(true, 'Drop the database schema to \'down\' this migration.');
    }
}
