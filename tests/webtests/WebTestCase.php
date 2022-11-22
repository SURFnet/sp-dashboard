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
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Surfnet\ServiceProviderDashboard\Webtests\Manage\Client\FakeTeamsQueryClient;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\RedirectResponse;

class WebTestCase extends SymfonyWebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
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
        // Disable notices, strict and deprecated warnings. Many Symfony 3.4 deprecation warnings are still to be fixed.
        Debug::enable(E_RECOVERABLE_ERROR & ~E_DEPRECATED, false);

        $this->client = static::createClient(
            [],
            [
                'HTTPS' => 'on',
            ]
        );

        $this->client->disableReboot();
        $this->testQueryClient = $this->client
            ->getContainer()
            ->get('Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient');
        $this->prodQueryClient = $this->client
            ->getContainer()
            ->get('surfnet.manage.client.query_client.prod_environment');
        $this->prodIdPClient = $this->client->getContainer()->get('Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\IdentityProviderClient');
        $this->testIdPClient = $this->client
            ->getContainer()
            ->get('surfnet.manage.client.identity_provider_client.test_environment');
        $this->prodPublicationClient = $this->client
            ->getContainer()
            ->get('surfnet.manage.client.publish_client.prod_environment');
        $this->testPublicationClient = $this->client->getContainer()->get('Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient');
        $this->testDeleteClient = $this->client
            ->getContainer()
            ->get('surfnet.manage.client.delete_client.test_environment');
        $this->prodDeleteClient = $this->client
            ->getContainer()
            ->get('surfnet.manage.client.delete_client.prod_environment');
        $this->teamsQueryClient = $this->client
            ->getContainer()
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
        return $this->client->getContainer()->get('doctrine')->getManager();
    }

    /**
     * @param string $role
     * @param Service[] $services
     */
    protected function logIn($role = 'ROLE_ADMINISTRATOR', array $services = [])
    {
        $session = $this->client->getContainer()->get('session');

        $contact = new Contact('webtest:nameid:johndoe', 'johndoe@localhost', 'John Doe');

        if (empty($services)) {
            $services[] = new Service();
        }

        foreach ($services as $service) {
            $contact->addService($service);
        }

        $authenticatedToken = new SamlToken([$role]);
        $authenticatedToken->setUser(
            new Identity($contact)
        );

        $session->set('_security_saml_based', serialize($authenticatedToken));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());

        $this->client->getCookieJar()->set($cookie);
    }

    /**
     * @param $serviceName
     * @return \Symfony\Component\DomCrawler\Crawler
     * @throws \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     */
    protected function switchToService($serviceName)
    {
        $crawler = $this->client->request('GET', '/');
        $form = $crawler->filter('.service-switcher form')->form();

        $service  = $this->getServiceRepository()->findByName($serviceName);

        $form['service_switcher[selected_service_id]']->select($service->getId());

        $this->client->submit($form);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after selecting a service'
        );

        return $this->client->followRedirect();
    }

    /**
     * @return ServiceRepository
     */
    protected function getServiceRepository()
    {
        return $this->client->getContainer()->get(ServiceRepository::class);
    }

    /**
     * @return AuthorizationService
     */
    protected function getAuthorizationService()
    {
        return $this->client->getContainer()->get(AuthorizationService::class);
    }
}
