<?php

namespace Surfnet\ServiceProviderDashboard\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * A default value for the `surfconext_representative_approved` column was missing
 * in the Version20181107134447 migration. This is now added.
 *
 * The following default are set for:
 *
 *  | Column                                 | Value          | Matching Service entity constant
 *  |========================================|================|====================================
 *  | surfconext_representative_approved     | no             | Service::SURFCONEXT_APPROVED_NO
 */
class Version20190605093154 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql(
            sprintf(
                'UPDATE service SET 
                surfconext_representative_approved = "%s"
                WHERE surfconext_representative_approved IS NULL
                ',
                'no'
            )
        );
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // Nothing is executed because at this point the state before couldn't be determined
    }
}
