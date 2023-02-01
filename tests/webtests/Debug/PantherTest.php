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
}
