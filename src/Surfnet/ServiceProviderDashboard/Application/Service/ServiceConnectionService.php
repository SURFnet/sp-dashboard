<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet B.V.
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
namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Application\Exception\RuntimeException;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityConnection;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityConnectionCollection;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\EntityConnectionExport;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\InstitutionId;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ServiceConnectionService
{
    public function __construct(
        private readonly IdpService $testIdps,
        private readonly EntityService $entityService,
        private readonly IdpServiceInterface $idpService,
        private readonly ServiceService $serviceService,
    ) {
    }

    /**
     * @param array<Service> $services
     * @return EntityConnectionCollection
     */
    public function findByServices(array $services): EntityConnectionCollection
    {
        if (empty($services)) {
            throw new RuntimeException('No service provided');
        }
        $collection = new EntityConnectionCollection();

        foreach ($services as $service) {
            if ($service->getInstitutionId() === null) {
                continue;
            }
            $institutionId = new InstitutionId($service->getInstitutionId());
            $connectedOtherIdps = $this->getRemainingIdpConnections($institutionId);

            $collection->addIdpList($this->listTestIdps());
            $this->addEntitiesToCollection(
                $collection,
                $institutionId,
                $this->getTestIdpsIndexed(),
                $connectedOtherIdps,
            );
        }
        return $collection;
    }

    public function findByInstitutionId(InstitutionId $institutionId): EntityConnectionCollection
    {
        $collection = new EntityConnectionCollection();
        $collection->addIdpList($this->listTestIdps());
        $connectedOtherIdps = $this->getRemainingIdpConnections($institutionId);
        $this->addEntitiesToCollection(
            $collection,
            $institutionId,
            $this->getTestIdpsIndexed(),
            $connectedOtherIdps
        );
        return $collection;
    }

    /**
     * @return IdentityProvider[]
     */
    private function listTestIdps()
    {
        return $this->testIdps->createCollection()->testEntities();
    }

    /**
     * @param array<string, IdentityProvider> $testIdpsIndexed
     * @param array<string, IdentityProvider> $otherIdpsIndexed
     */
    private function addEntitiesToCollection(
        EntityConnectionCollection $collection,
        InstitutionId $institutionId,
        array $testIdpsIndexed,
        array $otherIdpsIndexed,
    ): void {
        $list = [];
        $entities = $this->entityService->findPublishedTestEntitiesByInstitutionId($institutionId);

        if ($entities === null) {
            $collection->addEntityConnections($list);
            return;
        }
        $allowedProtocols = [Constants::TYPE_SAML, Constants::TYPE_OPENID_CONNECT_TNG];
        foreach ($entities as $entity) {
            $metadata = $this->assertValidMetadata($entity);
            $team = $this->getTeam($metadata);
            $service = $this->serviceService->getServiceByTeamName($team);
            $supportContact = $this->composeContactString($entity, 'support');
            $technicalContact = $this->composeContactString($entity, 'technical');
            $adminContact = $this->composeContactString($entity, 'admin');
            if ($service === null || !in_array($entity->getProtocol()->getProtocol(), $allowedProtocols)) {
                // Skipping entities of which we do not know the service (team is not set on any of our Services)
                continue;
            }
            if ($entity->getAllowedIdentityProviders()->isAllowAll()) {
                $serviceName = $service->getOrganizationNameEn();
                $list[$entity->getId()] = new EntityConnection(
                    $metadata->getNameEn(),
                    $metadata->getEntityId() ?: '',
                    $serviceName,
                    $testIdpsIndexed,
                    $otherIdpsIndexed,
                    $testIdpsIndexed + $otherIdpsIndexed,
                    $supportContact,
                    $technicalContact,
                    $adminContact
                );
                continue;
            }

            $list[$entity->getId()] = new EntityConnection(
                $metadata->getNameEn(),
                $metadata->getEntityId() ?: '',
                $service->getOrganizationNameEn(),
                $testIdpsIndexed,
                $otherIdpsIndexed,
                $this->gatherConnectedIdps($entity, $testIdpsIndexed),
                $supportContact,
                $technicalContact,
                $adminContact
            );
        }
        $collection->addEntityConnections($list);
    }

    /**
     * @return array<string, IdentityProvider>
     */
    private function getTestIdpsIndexed(): array
    {
        // First create an indexed list of the test entities
        $testIdpsIndexed = [];
        $testIdps = $this->listTestIdps();

        foreach ($testIdps as $entity) {
            $testIdpsIndexed[$entity->getEntityId()] = $entity;
        }

        return $testIdpsIndexed;
    }

    private function assertValidMetadata(ManageEntity $entity): MetaData
    {
        $metadata = $entity->getMetaData();
        if ($metadata === null) {
            throw new RuntimeException(
                sprintf(
                    'No metadata available on entity with manage id: %s',
                    $entity->getId()
                )
            );
        }
        if ($metadata->getNameEn() === null) {
            throw new RuntimeException(
                sprintf(
                    'No name:en available for entity with manage id: %s',
                    $entity->getId()
                )
            );
        }
        return $metadata;
    }

    /**
     * @return array<string, IdentityProvider>
     */
    private function getRemainingIdpConnections(InstitutionId $institutionId)
    {
        $items = $this->idpService->findInstitutionIdps($institutionId);
        $indexed = [];
        foreach ($items->institutionEntities() as $idp) {
            $indexed[$idp->getEntityId()] = $idp;
        }
        return $indexed;
    }

    /**
     * @param array<string, IdentityProvider> $testIdpsIndexed
     * @return array<string, IdentityProvider>
     */
    private function gatherConnectedIdps(ManageEntity $entity, array $testIdpsIndexed): array
    {
        $connectedIdps = [];
        foreach ($entity->getAllowedIdentityProviders()->getAllowedIdentityProviders() as $identityProvider) {
            if (array_key_exists($identityProvider, $testIdpsIndexed)) {
                $connectedIdps[$identityProvider] = $testIdpsIndexed[$identityProvider];
            }
        }
        return $connectedIdps;
    }

    private function getTeam(MetaData $metadata): string
    {
        $team = $metadata->getCoin()->getServiceTeamId();
        if ($team === null) {
            // Skipping entities that do not have a team id
            throw new RuntimeException('No teamid is set on the Manage Entity, unable to continue');
        }
        return $team;
    }

    private function composeContactString(ManageEntity $entity, string $type): string
    {
        $data = match ($type) {
            'support' => $entity->getMetaData()?->getContacts()?->findSupportContact(),
            'technical' => $entity->getMetaData()?->getContacts()?->findTechnicalContact(),
            'admin' => $entity->getMetaData()?->getContacts()?->findAdministrativeContact(),
            default => throw new RuntimeException(sprintf('Cannot get contact information for type: %s', $type)),
        };
        if ($data === null) {
            return '';
        }
        return sprintf('%s %s (%s)', $data->getGivenName(), $data->getSurName(), $data->getEmail());
    }

    /**
     * @return array<EntityConnectionExport>
     */
    public function getExportData(InstitutionId $institutionId): array
    {
        $data = $this->findByInstitutionId($institutionId);
        $exportData = [];
        foreach ($data->export() as $entity) {
            $exportValueObject = new EntityConnectionExport();
            $exportValueObject->entityId = $entity->entityId;
            $exportValueObject->nameOfEntity = $entity->entityName;
            $exportValueObject->nameOfService = $entity->vendorName;
            $exportValueObject->supportContact = $entity->supportContact;
            $exportValueObject->adminContact = $entity->administativeContact;
            $exportValueObject->technicalContact = $entity->technicalContact;
            $exportValueObject->idps = $entity->availableIdps();
            $exportData[] = $exportValueObject;
        }
        return $exportData;
    }
}
