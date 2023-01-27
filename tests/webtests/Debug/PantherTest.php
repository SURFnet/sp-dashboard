<?php

use Surfnet\ServiceProviderDashboard\Webtests\Debug\DebugFile;
use Surfnet\ServiceProviderDashboard\Webtests\WebTestCase;
use Symfony\Component\Panther\PantherTestCase;

class PantherTest extends WebTestCase
{
    public function testLogging(): void
    {
        parent::setUp();

        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        try {
            $this->switchToService('Ibuildings B.V.');
        } catch(Exception $e) {
            echo $e->getMessage();
        } finally {
            $this->client->close();
        }
    }

//    public static function setUpBeforeClass(): void
//    {
//        exec('cd /Users/sjaakkallemein/src/sp-dashboard && composer dump-env test', $output, $resultCode);
//    }
//
//    public static function tearDownAfterClass(): void
//    {
//        exec('rm /Users/sjaakkallemein/root/.env.local.php', $output, $resultCode);
//    }
}
