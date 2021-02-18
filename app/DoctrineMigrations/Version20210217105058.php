<?php

namespace Application\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Remove no longer tracked organization display name and organization url from Entity entity
 */
class Version20210217105058 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity DROP organization_display_name_en, DROP organization_url_en, DROP organization_display_name_nl, DROP organization_url_nl');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE entity ADD organization_display_name_en LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD organization_url_en LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD organization_display_name_nl LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci, ADD organization_url_nl LONGTEXT DEFAULT NULL COLLATE utf8_unicode_ci');
    }
}
