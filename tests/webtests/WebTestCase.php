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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Authentication\Token\SamlToken;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as SymfonyWebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\StringInput;

class WebTestCase extends SymfonyWebTestCase
{
    /**
     * @var \Symfony\Bundle\FrameworkBundle\Client
     */
    protected $client;

    /**
     * @var \Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
     */
    private $serviceRepository;

    /**
     * @var \Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
     */
    private $supplierRepository;

    public function setUp()
    {
        $this->client = static::createClient(
            [],
            [
            'HTTPS' => 'on',
            ]
        );
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

    protected function logIn($role = 'ROLE_ADMINISTRATOR', Supplier $supplier = null)
    {
        $session = $this->client->getContainer()->get('session');

        $contact = new Contact('webtest:nameid:johndoe', 'johndoe@localhost', 'John Doe');

        if (!$supplier) {
            $supplier = new Supplier();
        }

        $contact->setSupplier($supplier);

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
     * @return SupplierRepository
     */
    protected function getSupplierRepository()
    {
        return $this->client->getContainer()->get('surfnet.dashboard.repository.supplier');
    }

    /**
     * @return ServiceRepository
     */
    protected function getServiceRepository()
    {
        return $this->client->getContainer()->get('surfnet.dashboard.repository.service');
    }

    /**
     * @return AuthorizationService
     */
    protected function getAuthorizationService()
    {
        return $this->client->getContainer()->get('surfnet.dashboard.service.authorization');
    }
}
