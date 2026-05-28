<?php

/**
 * Copyright 2026 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use GuzzleHttp\Exception\ClientException;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Throwable;

class ManageFixtures extends Fixture implements DependentFixtureInterface
{
    public function __construct(
        private readonly PublishEntityClient $publishClient,
        private readonly QueryClient $queryClient,
        private readonly LoggerInterface $logger,
    ) {
    }

    public function getDependencies(): array
    {
        return [WebTestFixtures::class];
    }

    public function load(ObjectManager $manager): void
    {
        /** @var ServiceRepository $serviceRepo */
        $serviceRepo = $manager->getRepository(Service::class);
        $contact = new Contact('fixture-user', 'fixture@example.org', 'Fixture User');

        $surfService = $serviceRepo->findByTeamName(WebTestFixtures::TEAMNAME_SURF);
        $ibuildingsService = $serviceRepo->findByTeamName(WebTestFixtures::TEAMNAME_IBUILDINGS);

        $samlSpEntityId = 'https://fixture-saml-sp.example.org/saml/metadata';
        $samlManageId = $this->findManageId($samlSpEntityId);
        $pristineSaml = $this->fetchPristine($samlManageId);
        $samlSp = $this->samlSp($pristineSaml !== null ? $samlManageId : null);
        if ($surfService !== null) {
            $samlSp->setService($surfService);
        }
        $this->publish($samlSp, $pristineSaml, $contact);

        $oidcRpEntityId = 'fixture-oidc-rp.example.org';
        $oidcManageId = $this->findManageId($oidcRpEntityId);
        $pristineOidc = $this->fetchPristine($oidcManageId);
        $oidcRp = $this->oidcRpConfidential($pristineOidc !== null ? $oidcManageId : null);
        if ($ibuildingsService !== null) {
            $oidcRp->setService($ibuildingsService);
        }
        $this->publish($oidcRp, $pristineOidc, $contact);

        $oauthCcEntityId = 'fixture-oauth-cc.example.org';
        $oauthManageId = $this->findManageId($oauthCcEntityId);
        $pristineOauth = $this->fetchPristine($oauthManageId);
        $oauthCc = $this->oauthClientCredentials($pristineOauth !== null ? $oauthManageId : null);
        if ($ibuildingsService !== null) {
            $oauthCc->setService($ibuildingsService);
        }
        $this->publish($oauthCc, $pristineOauth, $contact);
    }

    private function findManageId(string $entityId): ?string
    {
        try {
            return $this->queryClient->findManageIdByEntityId($entityId);
        } catch (Throwable) {
            return null;
        }
    }

    private function fetchPristine(?string $manageId): ?ManageEntity
    {
        if ($manageId === null) {
            return null;
        }
        try {
            return $this->queryClient->findByManageId($manageId);
        } catch (Throwable) {
            return null;
        }
    }

    private function publish(ManageEntity $entity, ?ManageEntity $pristine, Contact $contact): void
    {
        try {
            $this->publishClient->publish($entity, $pristine, $contact);
        } catch (Throwable $e) {
            $detail = $e->getMessage();
            $inner = $e;
            while ($inner !== null) {
                if ($inner instanceof ClientException) {
                    $detail = (string) $inner->getResponse()->getBody();
                    break;
                }
                $inner = $inner->getPrevious();
            }
            $this->logger->warning(sprintf(
                'ManageFixtures: could not register "%s" in Manage (%s). Is Manage running?',
                $entity->getMetaData()?->getEntityId(),
                $detail,
            ));
        }
    }

    private function samlSp(?string $existingId = null): ManageEntity
    {
        return ManageEntity::fromApiResponse([
            'id'   => $existingId,
            'type' => 'saml20_sp',
            'data' => [
                'entityid'        => 'https://fixture-saml-sp.example.org/saml/metadata',
                'active'          => true,
                'state'           => 'testaccepted',
                'allowedall'      => true,
                'allowedEntities' => [],
                'arp'             => ['enabled' => false, 'attributes' => []],
                'metaDataFields'  => [
                    'name:en'                                  => 'Fixture SAML SP',
                    'name:nl'                                  => 'Fixture SAML SP',
                    'description:en'                           => 'Fixture SAML service provider',
                    'description:nl'                           => 'Fixture SAML service provider',
                    'OrganizationName:en'                      => 'Fixture Organization',
                    'logo:0:url'           => 'https://spdashboard.dev.openconext.local/images/surfconext-logo.png',
                    'logo:0:width'         => 100,
                    'logo:0:height'        => 100,
                    'coin:ss:type_of_service:en'               => 'Education',
                    'coin:ss:type_of_service:nl'               => 'Onderwijs',
                    'AssertionConsumerService:0:Binding'       => 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
                    'AssertionConsumerService:0:Location'      => 'https://fixture-saml-sp.example.org/saml/acs',
                    'NameIDFormat'                             => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
                    'coin:service_team_id'                     => WebTestFixtures::TEAMNAME_SURF,
                    'contacts:0:contactType'                   => 'support',
                    'contacts:0:givenName'                     => 'Fixture',
                    'contacts:0:surName'                       => 'User',
                    'contacts:0:emailAddress'                  => 'fixture@example.org',
                    'contacts:1:contactType'                   => 'technical',
                    'contacts:1:givenName'                     => 'Fixture',
                    'contacts:1:surName'                       => 'User',
                    'contacts:1:emailAddress'                  => 'fixture@example.org',
                ],
            ],
        ]);
    }

    private function oidcRpConfidential(?string $existingId = null): ManageEntity
    {
        return ManageEntity::fromApiResponse([
            'id'   => $existingId,
            'type' => 'oidc10_rp',
            'data' => [
                'entityid'        => 'fixture-oidc-rp.example.org',
                'active'          => true,
                'state'           => 'testaccepted',
                'allowedall'      => true,
                'allowedEntities' => [],
                'arp'             => ['enabled' => false, 'attributes' => []],
                'metaDataFields'  => [
                    'name:en'              => 'Fixture OIDC RP',
                    'name:nl'              => 'Fixture OIDC RP',
                    'description:en'       => 'Fixture OIDC confidential client',
                    'description:nl'       => 'Fixture OIDC confidential client',
                    'OrganizationName:en'  => 'Fixture Organization',
                    'logo:0:url'           => 'https://spdashboard.dev.openconext.local/images/surfconext-logo.png',
                    'logo:0:width'         => 100,
                    'logo:0:height'        => 100,
                    'coin:ss:type_of_service:en' => 'Education',
                    'coin:ss:type_of_service:nl' => 'Onderwijs',
                    'NameIDFormat'         => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
                    'redirectUrls'         => ['https://fixture-oidc-rp.example.org/callback'],
                    'grants'               => ['authorization_code', 'refresh_token'],
                    'isPublicClient'       => false,
                    'secret'               => 'fixture-oidc-rp-secret',
                    'accessTokenValidity'  => 3600,
                    'coin:service_team_id' => WebTestFixtures::TEAMNAME_IBUILDINGS,
                    'contacts:0:contactType'  => 'support',
                    'contacts:0:givenName'    => 'Fixture',
                    'contacts:0:surName'      => 'User',
                    'contacts:0:emailAddress' => 'fixture@example.org',
                    'contacts:1:contactType'  => 'technical',
                    'contacts:1:givenName'    => 'Fixture',
                    'contacts:1:surName'      => 'User',
                    'contacts:1:emailAddress' => 'fixture@example.org',
                ],
            ],
        ]);
    }

    private function oauthClientCredentials(?string $existingId = null): ManageEntity
    {
        return ManageEntity::fromApiResponse([
            'id'   => $existingId,
            'type' => 'oauth20_ccc',
            'data' => [
                'entityid'        => 'fixture-oauth-cc.example.org',
                'active'          => true,
                'state'           => 'testaccepted',
                'allowedall'      => true,
                'allowedEntities' => [],
                'arp'             => ['enabled' => false, 'attributes' => []],
                'metaDataFields'  => [
                    'name:en'              => 'Fixture OAuth CC',
                    'name:nl'              => 'Fixture OAuth CC',
                    'description:en'       => 'Fixture OAuth client credentials client',
                    'description:nl'       => 'Fixture OAuth client credentials client',
                    'OrganizationName:en'  => 'Fixture Organization',
                    'logo:0:url'           => 'https://spdashboard.dev.openconext.local/images/surfconext-logo.png',
                    'logo:0:width'         => 100,
                    'logo:0:height'        => 100,
                    'NameIDFormat'         => 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient',
                    'grants'               => ['client_credentials'],
                    'secret'               => 'fixture-oauth-cc-secret',
                    'accessTokenValidity'  => 3600,
                    'coin:service_team_id' => WebTestFixtures::TEAMNAME_IBUILDINGS,
                    'contacts:0:contactType'  => 'support',
                    'contacts:0:givenName'    => 'Fixture',
                    'contacts:0:surName'      => 'User',
                    'contacts:0:emailAddress' => 'fixture@example.org',
                    'contacts:1:contactType'  => 'technical',
                    'contacts:1:givenName'    => 'Fixture',
                    'contacts:1:surName'      => 'User',
                    'contacts:1:emailAddress' => 'fixture@example.org',
                ],
            ],
        ]);
    }
}
