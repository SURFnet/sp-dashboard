<?php

/**
 * Copyright 2017 SURFnet B.V.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace Surfnet\ServiceProviderDashboard\Webtests;

use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\Remote\DesiredCapabilities;
use RuntimeException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteManageEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryManageRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryTeamsRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Webtests\Debug\DebugFile;
use Surfnet\ServiceProviderDashboard\Webtests\Manage\Client\FakeTeamsQueryClient;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use function dd;

class WebTestCase extends PantherTestCase
{
    /**
     * @var \Symfony\Component\Panther\Client;
     */
    protected $client;
    /**
     * @var QueryManageRepository
     */
    protected $prodQueryClient;
    /**
     * @var QueryManageRepository
     */
    protected $testQueryClient;
    /**
     * @var IdentityProviderRepository
     */
    private $testIdPClient;
    /**
     * @var IdentityProviderRepository
     */
    private $prodIdPClient;
    /**
     * @var PublishEntityRepository
     */
    protected $prodPublicationClient;
    /**
     * @var PublishEntityRepository
     */
    protected $testPublicationClient;
    /**
     * @var DeleteManageEntityRepository
     */
    protected $prodDeleteClient;
    /**
     * @var DeleteManageEntityRepository
     */
    protected $testDeleteClient;

    /** @var QueryTeamsRepository&FakeTeamsQueryClient */
    protected $teamsQueryClient;

    public function setUp(): void
    {
        self::ensureKernelShutdown();
//        $this->client = Client::createChromeClient(null, ['--headless', '--disable-gpu', '--disable-dev-shm-usage', '--no-sandbox']);

        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments(['--disable-dev-shm-usage', '--disable-gpu', '--headless', '--no-sandbox']);
        $this->client = Client::createSeleniumClient(
            'http://test-browser:4444/wd/hub',
            $chromeOptions->toCapabilities(),
            'https://spdashboard.vm.openconext.org'
        );

        $this->testQueryClient = self::getContainer()
            ->get('Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient');
        $this->prodQueryClient =  self::getContainer()
            ->get('surfnet.manage.client.query_client.prod_environment');
        $this->prodIdPClient = self::getContainer()
            ->get('Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\IdentityProviderClient');
        $this->testIdPClient =  self::getContainer()
            ->get('surfnet.manage.client.identity_provider_client.test_environment');
        $this->prodPublicationClient = self::getContainer()
            ->get('surfnet.manage.client.publish_client.prod_environment');
        $this->testPublicationClient = self::getContainer()
            ->get('Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient');
        $this->testDeleteClient = self::getContainer()
            ->get('surfnet.manage.client.delete_client.test_environment');
        $this->prodDeleteClient = self::getContainer()
            ->get('surfnet.manage.client.delete_client.prod_environment');
        $this->teamsQueryClient = self::getContainer()
            ->get('Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\QueryClient');
    }

    protected function registerManageEntity(
        string $env,
        string $protocol,
        string $id,
        string $name,
        string $entityId,
        ?string $metadataUrl = null,
        ?string $teamName = null
    ) {
        switch ($protocol) {
            case "saml20_sp":
            case "oidc10_rp":
            case "oauth20_ccc":
                $this->registerSp(
                    $env,
                    $protocol,
                    $id,
                    $name,
                    $entityId,
                    $metadataUrl,
                    $teamName
                );
                break;
            case "saml20_idp":
                $this->registerIdP($env, $protocol, $id, $name, $entityId);
                break;
        }
    }

    protected function registerManageEntityRaw(string $env, string $json)
    {
        switch ($env) {
            case "production":
                $this->prodQueryClient->registerEntityRaw($json);
                break;
            case "test":
                $this->testQueryClient->registerEntityRaw($json);
                break;
            default:
                throw new RuntimeException('Unsupported environment');
        }
    }

    private function registerSp(string $env, string $protocol, string $id, string $name, string $entityId, ?string $metadataUrl = null, ?string $teamName = null)
    {
        switch ($env) {
            case "production":
                $this->prodQueryClient->registerEntity(
                    $protocol,
                    $id,
                    $entityId,
                    $metadataUrl,
                    $name,
                    $teamName
                );
                break;
            case "test":
                $this->testQueryClient->registerEntity(
                    $protocol,
                    $id,
                    $entityId,
                    $metadataUrl,
                    $name,
                    $teamName
                );
                break;
            default:
                throw new RuntimeException('Unsupported environment');
        }
    }

    private function registerIdP(string $env, string $protocol, string $id, string $name, string $entityId)
    {
        switch ($env) {
            case "production":
                $this->prodIdPClient->registerEntity(
                    $protocol,
                    $id,
                    $entityId,
                    $name
                );
                break;
            case "test":
                $this->testIdPClient->registerEntity(
                    $protocol,
                    $id,
                    $entityId,
                    $name
                );
                break;
            default:
                throw new RuntimeException('Unsupported environment');
        }
    }

    protected function loadFixtures()
    {
        $em = $this->getEntityManager();

        $loader = new Loader();
        $loader->addFixture(new WebTestFixtures);

        $purger = new ORMPurger($em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($em, $purger);

        // The sequence of the Service table is important, purger only removes data, does not reset the
        // autoincrement sequence. That is explicitly reset with the query below.
        // Preferably we'de use $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE); but that does not seem
        // to work with SQLite
        //$em->getConnection()->exec("UPDATE SQLITE_SEQUENCE SET SEQ=0 WHERE NAME='service';");

        $executor->execute($loader->getFixtures());
    }

    protected function clearFixtures()
    {
        $em = $this->getEntityManager();

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute([]);
    }

    /**
     * @return EntityManager
     */
    private function getEntityManager()
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    /**
     * @param string $role
     * @param Service[] $services
     */
    protected function logIn($role = 'ROLE_ADMINISTRATOR', array $services = [])
    {
        $contact = new Contact('webtest:nameid:johndoe', 'johndoe@localhost', 'John Doe');

        if (empty($services)) {
            $services[] = new Service();
        }

        foreach ($services as $service) {
            $contact->addService($service);
        }
        $contact->assignRole($role);

        $crawler = $this->client->request('GET', 'https://spdashboard.vm.openconext.org');

        DebugFile::dumpHtml($crawler->html(),'debugfile6.html');
        $form = $crawler
            ->selectButton('Log in')
            ->form();

        $form->setValues([
            'username' => 'admin',
            'password' => ''
        ]);

        $crawler = $this->client->submit($form);
    }

    /**
     * @param $serviceName
     * @return \Symfony\Component\DomCrawler\Crawler
     * @throws \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     */
    protected function switchToService($serviceName)
    {
        $crawler = $this->client->request('GET', 'https://spdashboard.vm.openconext.org');

        DebugFile::dumpHtml($crawler->html(), 'debugfile5.html');

        //$this->client->waitFor('service_switcher[selected_service_id]');

        $form = $crawler->filter('.service-switcher form')->form();

        $service = $this->getServiceRepository()->findByName($serviceName);

        //$this->client->waitFor('service_switcher[selected_service_id]');

        $form['service_switcher[selected_service_id]']->select($service->getId());

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after selecting a service'
        );

        $this->client->followRedirects();

        return $crawler;
    }

    /**
     * @return ServiceRepository
     */
    protected function getServiceRepository()
    {
        return self::getContainer()->get(ServiceRepository::class);
    }

    /**
     * @return AuthorizationService
     */
    protected function getAuthorizationService()
    {
        return self::getContainer()->get(AuthorizationService::class);
    }
}
