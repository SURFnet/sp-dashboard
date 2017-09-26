<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170926095233 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE contact (id INT AUTO_INCREMENT NOT NULL, supplier_id INT NOT NULL, name_id VARCHAR(150) NOT NULL, display_name VARCHAR(255) NOT NULL, email_address VARCHAR(255) NOT NULL, INDEX IDX_4C62E6382ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE supplier (id INT AUTO_INCREMENT NOT NULL, guid CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\', name VARCHAR(255) NOT NULL, team_name VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_9B2A6C7E2B6FCFB2 (guid), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE service (id INT AUTO_INCREMENT NOT NULL, supplier_id INT NOT NULL, archived TINYINT(1) NOT NULL, environment VARCHAR(255) NOT NULL, status INT NOT NULL, created DATETIME NOT NULL, updated DATETIME NOT NULL, ticket_no VARCHAR(255) NOT NULL, janus_id VARCHAR(255) DEFAULT NULL, import_url VARCHAR(255) DEFAULT NULL, metadata_url VARCHAR(255) DEFAULT NULL, metadata_xml LONGTEXT DEFAULT NULL, acs_location VARCHAR(255) DEFAULT NULL, entity_id VARCHAR(255) DEFAULT NULL, certificate LONGTEXT DEFAULT NULL, logo_url VARCHAR(255) DEFAULT NULL, name_nl VARCHAR(255) DEFAULT NULL, name_en VARCHAR(255) DEFAULT NULL, description_nl LONGTEXT DEFAULT NULL, description_en LONGTEXT DEFAULT NULL, application_url VARCHAR(255) DEFAULT NULL, eula_url VARCHAR(255) DEFAULT NULL, administrative_contact LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', technical_contact LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', support_contact LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', given_name_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', sur_name_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', common_name_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', display_name_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', email_address_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', organization_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', organization_type_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', affiliation_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', entitlement_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', principle_name_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', uid_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', preferred_language_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', personal_code_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', scoped_affiliation_attribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', edu_person_targeted_idattribute LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\', comments LONGTEXT DEFAULT NULL, INDEX IDX_E19D9AD22ADD6D8C (supplier_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE contact ADD CONSTRAINT FK_4C62E6382ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
        $this->addSql('ALTER TABLE service ADD CONSTRAINT FK_E19D9AD22ADD6D8C FOREIGN KEY (supplier_id) REFERENCES supplier (id)');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE contact DROP FOREIGN KEY FK_4C62E6382ADD6D8C');
        $this->addSql('ALTER TABLE service DROP FOREIGN KEY FK_E19D9AD22ADD6D8C');
        $this->addSql('DROP TABLE contact');
        $this->addSql('DROP TABLE supplier');
        $this->addSql('DROP TABLE service');
    }
}
