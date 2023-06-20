<?php

declare(strict_types=1);

namespace Surfnet\ServiceProviderDashboard\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230620124841 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE privacy_questions DROP certification, DROP certification_location, DROP certification_valid_from, DROP certification_valid_to, DROP surfmarket_dpa_agreement, DROP surfnet_dpa_agreement, DROP sn_dpa_why_not, DROP privacy_policy, DROP privacy_policy_url');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE privacy_questions ADD certification TINYINT(1) DEFAULT NULL, ADD certification_location VARCHAR(255) DEFAULT NULL, ADD certification_valid_from DATE DEFAULT NULL, ADD certification_valid_to DATE DEFAULT NULL, ADD surfmarket_dpa_agreement TINYINT(1) DEFAULT NULL, ADD surfnet_dpa_agreement TINYINT(1) DEFAULT NULL, ADD sn_dpa_why_not LONGTEXT DEFAULT NULL, ADD privacy_policy TINYINT(1) DEFAULT NULL, ADD privacy_policy_url VARCHAR(255) DEFAULT NULL');
    }
}
