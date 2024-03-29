<?php declare(strict_types=1);

namespace Surfnet\ServiceProviderDashboard\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20211013095152 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema): void
    {
	// this up() migration is auto-generated, please modify it to your needs
	$this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');
        $this->addSql('
        CREATE TABLE `sessions` (
        `sess_id` VARBINARY(128) NOT NULL PRIMARY KEY,
        `sess_data` BLOB NOT NULL,
        `sess_lifetime` INTEGER UNSIGNED NOT NULL,
        `sess_time` INTEGER UNSIGNED NOT NULL,
        INDEX `sessions_sess_lifetime_idx` (`sess_lifetime`)
	) COLLATE utf8mb4_bin, ENGINE = InnoDB;
        ');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE `sessions`');
    }
}
