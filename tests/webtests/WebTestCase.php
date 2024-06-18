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

use Closure;
use Doctrine\Common\DataFixtures\Executor\ORMExecutor;
use Doctrine\Common\DataFixtures\Loader;
use Doctrine\Common\DataFixtures\Purger\ORMPurger;
use Doctrine\ORM\EntityManager;
use Facebook\WebDriver\Chrome\ChromeOptions;
use Facebook\WebDriver\WebDriverBy;
use Facebook\WebDriver\WebDriverElement;
use PHPUnit\Framework\ExpectationFailedException;
use RuntimeException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\DeleteManageEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\IdentityProviderRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryManageRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryTeamsRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Repository\DevelopmentIssueRepository;
use Surfnet\ServiceProviderDashboard\Webtests\Debug\DebugFile;
use Surfnet\ServiceProviderDashboard\Webtests\Manage\Client\FakeTeamsQueryClient;
use Symfony\Component\BrowserKit\AbstractBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\PantherTestCase;
use Symfony\Component\Panther\ServerExtension;

class WebTestCase extends PantherTestCase
{
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

    protected DevelopmentIssueRepository $jiraIssueRepository;

    private string $surfConextRepresentativeAttributeName = '';
    private string $surfConextRepresentativeAttributeValue = '';

    public static function setUpBeforeClass(): void
    {
        exec('cd /var/www/html && composer dump-env test -q && chmod 777 /tmp/spdashboard-webtests.sqlite');
    }

    public static function tearDownAfterClass(): void
    {
        exec('rm /var/www/html/.env.local.php', $output, $resultCode);
    }

    public function dumpHtml(bool $screenShot = false)
    {
        static $no = 0;
        if ($screenShot) {
            self::$pantherClient->takeScreenshot(sprintf('/var/www/html/debug%d.png', $no));
        }
        DebugFile::dumpHtml(self::$pantherClient->getCrawler()->html(), sprintf('debug%d.html', $no++));
    }

    public function setUp(): void
    {
        self::stopWebServer();

        $chromeOptions = new ChromeOptions();
        $chromeOptions->addArguments([
                '--headless',
                '--no-sandbox',
                '--browser-test',
                '--disable-dev-shm-usage',
                '--disable-gpu',
                '--window-size=1920,1920',
            ]
        );
        $capabilities = $chromeOptions->toCapabilities();
        $capabilities->setCapability('acceptSslCerts', true);
        $capabilities->setCapability('acceptInsecureCerts', true);
        self::$pantherClient = self::$pantherClients[0] = Client::createSeleniumClient(
            'http://test-browser:4444/wd/hub',
            $capabilities,
            'https://spdashboard.dev.openconext.local'
        );
        ServerExtension::registerClient(self::$pantherClient);
        // you can avoid having to use Closure::bind if you use PantherTestCaseTrait directly
        Closure::bind(function(AbstractBrowser $client) {
            // contrary to the name, calling it with argument will set local static variable inside getClient method
            self::getClient($client);
        }, null, PantherTestCase::class)(self::$pantherClient);

        $this->testQueryClient = self::getContainer()
            ->get('Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient');
        $this->testQueryClient->reset();
        $this->prodQueryClient = self::getContainer()
            ->get('surfnet.manage.client.query_client.prod_environment');
        $this->prodQueryClient->reset();
        $this->prodIdPClient = self::getContainer()
            ->get('Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\IdentityProviderClient');
        $this->testIdPClient = self::getContainer()
            ->get('surfnet.manage.client.identity_provider_client.test_environment');
        $this->prodPublicationClient = self::getContainer()
            ->get('surfnet.manage.client.publish_client.prod_environment');
        $this->prodPublicationClient->reset();
        $this->testPublicationClient = self::getContainer()
            ->get('Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient');
        $this->testPublicationClient->reset();
        $this->testDeleteClient = self::getContainer()
            ->get('surfnet.manage.client.delete_client.test_environment');
        $this->testDeleteClient->reset();
        $this->prodDeleteClient = self::getContainer()
            ->get('surfnet.manage.client.delete_client.prod_environment');
        $this->teamsQueryClient = self::getContainer()
            ->get('Surfnet\ServiceProviderDashboard\Infrastructure\Teams\Client\QueryClient');
        $this->teamsQueryClient->reset();
        $this->jiraIssueRepository = self::getContainer()
            ->get('surfnet.dashboard.repository.issue');
        $this->surfConextRepresentativeAttributeName = self::getContainer()
            ->getParameter('surfnet.dashboard.security.authentication.authorization_attribute_name');
        $this->surfConextRepresentativeAttributeValue = self::getContainer()
            ->getParameter('surfnet.dashboard.security.authentication.surfconext_responsible_authorization');
    }

    protected function registerManageEntity(
        string $env,
        string $protocol,
        string $id,
        string $name,
        string $entityId,
        ?string $metadataUrl = null,
        ?string $teamName = null,
        ?string $institutionId = '',
    ) {
        switch ($protocol) {
            case 'saml20_sp':
            case 'oidc10_rp':
            case 'oauth20_ccc':
                $this->registerSp(
                    $env,
                    $protocol,
                    $id,
                    $name,
                    $entityId,
                    $metadataUrl,
                    $teamName,
                    $institutionId,
                );
                break;
            case 'saml20_idp':
                $this->registerIdP($env, $protocol, $id, $name, $entityId, $institutionId);
                break;
        }
    }

    protected function registerManageEntityRaw(string $env, string $json)
    {
        switch ($env) {
            case 'production':
                $this->prodQueryClient->registerEntityRaw($json);
                break;
            case 'test':
                $this->testQueryClient->registerEntityRaw($json);
                break;
            default:
                throw new RuntimeException('Unsupported environment');
        }
    }

    protected function createjiraTicket(string $entityId, string $issueType)
    {
        $ticket = new Ticket(
            $entityId,
            $entityId,
            'Name',
            'TranslationKey',
            'DescriptionTransKey',
            'John Doe',
            'jdoe@example.com',
            $issueType
        );
        $this->jiraIssueRepository->createIssueFrom($ticket);
    }

    private function registerSp(
        string $env,
        string $protocol,
        string $id,
        string $name,
        string $entityId,
        ?string $metadataUrl = null,
        ?string $teamName = null,
        ?string $institutionId = '',
    ) {
        switch ($env) {
            case 'production':
                $this->prodQueryClient->registerEntity(
                    $protocol,
                    $id,
                    $entityId,
                    $metadataUrl,
                    $name,
                    $teamName,
                    $institutionId
                );
                break;
            case 'test':
                $this->testQueryClient->registerEntity(
                    $protocol,
                    $id,
                    $entityId,
                    $metadataUrl,
                    $name,
                    $teamName,
                    $institutionId
                );
                break;
            default:
                throw new RuntimeException('Unsupported environment');
        }
    }

    private function registerIdP(string $env, string $protocol, string $id, string $name, string $entityId, string $institutionId = '')
    {
        switch ($env) {
            case 'production':
                $this->prodIdPClient->registerEntity(
                    $protocol,
                    $id,
                    $entityId,
                    $name,
                    $institutionId
                );
                break;
            case 'test':
                $this->testIdPClient->registerEntity(
                    $protocol,
                    $id,
                    $entityId,
                    $name,
                    $institutionId
                );
                break;
            default:
                throw new RuntimeException('Unsupported environment');
        }
    }

    protected function loadFixtures(): void
    {
        $em = $this->getEntityManager();

        $loader = new Loader();
        $loader->addFixture(new WebTestFixtures);

        $purger = new ORMPurger($em);
        $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE);
        $executor = new ORMExecutor($em, $purger);

        // The sequence of the Service table is important, purger only removes data, does not reset the
        // autoincrement sequence. That is explicitly reset with the query below.
        // Preferably we'd use $purger->setPurgeMode(ORMPurger::PURGE_MODE_TRUNCATE); but that does not seem
        // to work with SQLite
        $em->getConnection()->executeStatement("UPDATE SQLITE_SEQUENCE SET SEQ=0 WHERE NAME='service';");

        $executor->execute($loader->getFixtures());

        $this->teamsQueryClient->registerTeam('demo:openconext:org:surf.nl', '{"teamId": 1}');
        $this->teamsQueryClient->registerTeam('demo:openconext:org:ibuildings.nl', '{"teamId": 2}');
    }

    protected function clearFixtures(): void
    {
        $em = $this->getEntityManager();

        $purger = new ORMPurger($em);
        $executor = new ORMExecutor($em, $purger);
        $executor->execute([]);
    }

    private function getEntityManager(): EntityManager
    {
        return self::getContainer()->get('doctrine')->getManager();
    }

    protected function logOut()
    {
        self::$pantherClient->restart();
    }

    protected function logIn(Service $service = null, Service $secondService = null): Crawler
    {
        $crawler = self::$pantherClient->request('GET', 'https://spdashboard.dev.openconext.local');

        $form = $crawler->findElement(WebDriverBy::cssSelector('form.login-form'));
        $this->fillFormField($form, '#username', 'John Doe');
        $this->fillFormField($form, '#password', 'secret');
        // By default, log in using an admin team (see .env.test > administrator_teams)
        $teamName = 'urn:collab:group:dev.openconext.local:dev:openconext:local:spd_admin';
        if ($service) {
            $teamName = $service->getTeamName();
        }
        $select = $crawler->filterXPath(".//select[@id='add-attribute']//option[@value='urn:mace:dir:attribute-def:isMemberOf']");
        $select->click();
        $isMemberOf = $crawler->filter('input[name="urn:mace:dir:attribute-def:isMemberOf"]');
        $isMemberOf->sendKeys($teamName);

        if ($secondService) {
            $secondTeamName = $secondService->getTeamName();
            $select = $crawler->filterXPath(".//select[@id='add-attribute']//option[@value='urn:mace:dir:attribute-def:isMemberOf']");
            $select->click();
            $isMemberOf = $crawler->filter('input[name="urn:mace:dir:attribute-def:isMemberOf"]')->eq(1);
            $isMemberOf->sendKeys($secondTeamName);
        }

        return $this->finishLogin();
    }

    protected function logInSurfConextResponsible(string $institutionId): void
    {
        $crawler = self::$pantherClient->request('GET', 'https://spdashboard.dev.openconext.local');

        $form = $crawler->findElement(WebDriverBy::cssSelector('form.login-form'));
        $this->fillFormField($form, '#username', 'John Dart');
        $this->fillFormField($form, '#password', 'secret');

        $select = $crawler->filterXPath(
            sprintf(
                ".//select[@id='add-attribute']//option[@value='urn:mace:dir:attribute-def:%s']",
                $this->surfConextRepresentativeAttributeName
            )
        );
        $select->click();
        $entitlement = $crawler->filter(sprintf('input[name="urn:mace:dir:attribute-def:%s"]', $this->surfConextRepresentativeAttributeName));
        $entitlement->sendKeys('urn:mace:surfnet.nl:surfnet.nl:sab:organizationCode:' . $institutionId);
        // Now also send the attribute value that indicates this user is of role SurfConext representative
        $select = $crawler->filterXPath(
            sprintf(
                ".//select[@id='add-attribute']//option[@value='urn:mace:dir:attribute-def:%s']",
                $this->surfConextRepresentativeAttributeName
            )
        );
        $select->click();
        $entitlement = $crawler
            ->filter(sprintf('input[name="urn:mace:dir:attribute-def:%s"]', $this->surfConextRepresentativeAttributeName))
            ->eq(1); // There are now 2 entitlement attrs, pick the second

        $entitlement->sendKeys($this->surfConextRepresentativeAttributeValue);
        $this->finishLogin();
    }

    private function finishLogin(): Crawler
    {
        self::findBy('.button')->click();
        $crawler = self::$pantherClient->refreshCrawler();

        // Do we have a consent screen?
        if ($crawler->filter('.page__title')->count() > 0 && $crawler->filter('.page__title')->getText() === 'Review your information that will be shared.') {
            $crawler->filter('.cta_consent_ok')->click();
        }

        return $crawler;
    }

    /**
     * @param $serviceName
     * @return Crawler
     * @throws InvalidArgumentException
     */
    protected function switchToService($serviceName): Crawler
    {
        $service = $this->getServiceRepository()->findByName($serviceName);

        $crawler = self::$pantherClient->request('GET', 'https://spdashboard.dev.openconext.local');

        // Select the service drop down
        $crawler->filter('.service-switcher form')->click();

        // Select the service option
        $path = sprintf(
            "//li[contains(@id,'-%s')]",
            $service->getId()
        );
        $crawler->findElement(WebDriverBy::xpath($path))->click();

        self::$pantherClient->followRedirects();

        return $crawler;
    }

    protected static function assertOnPage(string $expectation, Crawler $crawler = null)
    {
        if (!$crawler) {
            $crawler = self::$pantherClient->refreshCrawler();
        }
        if (!str_contains($crawler->html(), $expectation)) {
            throw new ExpectationFailedException(
                sprintf(
                    'Expected text: "%s" was not found in the HTML of the current page',
                    $expectation
                )
            );
        }
        self::assertTrue(true);
    }

    protected static function assertNotOnPage(string $expectation, Crawler $crawler = null)
    {
        if (!$crawler) {
            $crawler = self::$pantherClient->refreshCrawler();
        }
        if (str_contains($crawler->html(), $expectation)) {
            throw new ExpectationFailedException(
                sprintf(
                    'Expected text: "%s" was not supposed to be found in the HTML of the current page',
                    $expectation
                )
            );
        }
        self::assertTrue(true);
    }

    protected static function findBy(string $cssSelector): WebDriverElement
    {
        return self::$pantherClient->findElement(WebDriverBy::cssSelector($cssSelector));
    }

    protected function fillFormField(WebDriverElement $form, string $targetField, string $value): void
    {
        $form->findElement(WebDriverBy::cssSelector($targetField))->clear();
        $form->findElement(WebDriverBy::cssSelector($targetField))->sendKeys($value);
    }

    protected function checkFormField(WebDriverElement $form, string $targetField): void
    {
        $form->findElement(WebDriverBy::cssSelector($targetField))->click();
    }

    protected function click(WebDriverElement $form, string $targetField): void
    {
        $form->findElement(WebDriverBy::cssSelector($targetField))->click();
    }

    protected function getServiceRepository(): ServiceRepository
    {
        return self::getContainer()->get(ServiceRepository::class);
    }

    protected function getAuthorizationService(): AuthorizationService
    {
        return self::getContainer()->get(AuthorizationService::class);
    }

    protected function screenshot(string $filename)
    {
        self::$pantherClient->takeScreenshot($filename);
    }
}
