<?php declare(strict_types=1);

namespace Surfnet\ServiceProviderDashboard\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Get rid of the cursed entity entity. This entity was already unused. Lots of unused code needed to be removed.
 */
class Version20210218132251 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE entity');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE entity (id CHAR(36) NOT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:guid)\', service_id INT NOT NULL, archived TINYINT(1) NOT NULL, environment VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, status VARCHAR(255) NOT NULL COLLATE utf8_unicode_ci, created DATETIME NOT NULL, updated DATETIME NOT NULL, manage_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, import_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, metadata_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, pasted_metadata LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, metadata_xml LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, name_id_format VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, acs_location VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, entity_id VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, certificate LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, logo_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, name_nl VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, name_en VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, description_nl LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, description_en LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, application_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, eula_url VARCHAR(255) DEFAULT NULL COLLATE utf8_unicode_ci, administrative_contact LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', technical_contact LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', support_contact LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', given_name_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', sur_name_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', common_name_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', display_name_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', email_address_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', organization_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', organization_type_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', affiliation_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', entitlement_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', principle_name_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', uid_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', preferred_language_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', personal_code_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', scoped_affiliation_attribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', edu_person_targeted_idattribute LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', comments LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, client_secret VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, redirect_uris LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:json_array)\', grant_type VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, protocol VARCHAR(50) DEFAULT NULL COLLATE utf8_unicode_ci, enable_playground TINYINT(1) DEFAULT NULL, is_public_client TINYINT(1) DEFAULT NULL, access_token_validity INT UNSIGNED DEFAULT NULL, oidcng_resource_servers LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci COMMENT \'(DC2Type:object)\', UNIQUE INDEX UNIQ_E284468BF396750 (id), INDEX IDX_E284468ED5CA9E6 (service_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE entity ADD CONSTRAINT FK_E284468ED5CA9E6 FOREIGN KEY (service_id) REFERENCES service (id)');
    }
}
