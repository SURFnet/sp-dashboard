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
use GuzzleHttp\Handler\MockHandler;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\RedirectResponse;

class WebTestCase extends SymfonyWebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var MockHandler
     */
    protected $testMockHandler;

    /**
     * @var MockHandler
     */
    protected $prodMockHandler;

    public function setUp()
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

        $this->testMockHandler = $this->client->getContainer()->get('surfnet.manage.http.guzzle.mock_handler');
        $this->prodMockHandler = $this->client->getContainer()->get('surfnet.manage.http.guzzle.mock_handler_prod');
    }

    protected function loadFixtures()
    {
        $em = $this->getEntityManager();

        $loader = new Loader();
        $loader->addFixture(new WebTestFixtures);

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
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
        $crawler = $this->client->request('GET', '/service/create');
        $form = $crawler->filter('.service-switcher')
            ->selectButton('Select')
            ->form();

        $form['service']->select(
            $this->getServiceRepository()->findByName($serviceName)->getId()
        );

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
     * @return EntityRepository
     */
    protected function getEntityRepository()
    {
        return $this->client->getContainer()->get(EntityRepository::class);
    }

    /**
     * @return AuthorizationService
     */
    protected function getAuthorizationService()
    {
        return $this->client->getContainer()->get(AuthorizationService::class);
    }
}
