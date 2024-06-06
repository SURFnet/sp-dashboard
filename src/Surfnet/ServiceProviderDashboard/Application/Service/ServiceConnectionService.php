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
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityConnection;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityConnectionCollection;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\InstitutionId;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\InstitutionIdNotFoundException;
use function in_array;

class ServiceConnectionService
{
    public function __construct(
        private readonly IdpService          $testIdps,
        private readonly EntityService       $entityService,
        private readonly IdpServiceInterface $idpService, private readonly ServiceService $serviceService,
    ) {
    }

    /**
     * @param array<string, Service> $service
     */
    public function find(array $services): EntityConnectionCollection
    {
        if (empty($services)) {
            throw new RuntimeException('No service provided');
        }
        $collection = new EntityConnectionCollection();
        $this->addIdpList($services, $collection);


        foreach ($services as $service) {
            $institutionId = new InstitutionId($service->getInstitutionId());
            // First create an indexed list of the test entities
            $testIdpsIndexed = [];
            $testIdps = $this->listTestIdps($institutionId);

            foreach ($testIdps as $entity) {
                $testIdpsIndexed[$entity->getEntityId()] = $entity;
            }
            $list = [];
            $entities = $this->entityService->findPublishedTestEntitiesByInstitutionId($institutionId);
            if ($entities === null) {
                return $list;
            }
            $allowedProtocols = [Constants::TYPE_SAML, Constants::TYPE_OPENID_CONNECT_TNG];
            foreach ($entities as $entity) {
                if (!in_array($entity->getProtocol()->getProtocol(), $allowedProtocols)) {
                    // Skipping irrelevant entity types
                    continue;
                }
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
                if ($entity->getAllowedIdentityProviders()->isAllowAll()) {
                    $list[$entity->getId()] = new EntityConnection(
                        $metadata->getNameEn(),
                        $service->getOrganizationNameEn(),
                        $testIdpsIndexed,
                        $testIdpsIndexed,
                    );
                    continue;
                }
                $connectedIdps = [];
                foreach ($entity->getAllowedIdentityProviders()->getAllowedIdentityProviders() as $identityProvider) {
                    if (array_key_exists($identityProvider, $testIdpsIndexed)) {
                        $connectedIdps[$identityProvider] = $testIdpsIndexed[$identityProvider];
                    }
                }

                $list[$entity->getId()] = new EntityConnection(
                    $metadata->getNameEn(),
                    $service->getOrganizationNameEn(),
                    $testIdpsIndexed,
                    $connectedIdps,
                );
            }
            $collection->addEntityConnections($list);
        }
        return $collection;
    }

    /**
     * @return IdentityProvider[]
     */
    private function listTestIdps(InstitutionId $institutionId)
    {
        $institutionEntities = $this->idpService->findInstitutionIdps($institutionId);
        $testEntities =  $this->testIdps->createCollection()->testEntities();
        return $testEntities + $institutionEntities->institutionEntities();
    }

    private function retrieveInstitutionId(
        Service $service,
        Contact $user
    ): InstitutionId {
        // The InstitutionId from the assertion is leading (if set)
        $id = $user->getInstitutionId();
        if ($id === null) {
            // Fall back on the institution id that might be configured on the Service (ORM) entity
            $id = $service->getInstitutionId();
        }
        if ($id === null) {
            throw new InstitutionIdNotFoundException(
                'ROLE_SURFCONEXT_RESPONSIBLE is granted, but no institution_id was provided in the assertion ' .
                'nor was it present in the Service entity'
            );
        }
        return new InstitutionId($id);
    }

    /**
     * @param array<Service> $services
     */
    private function addIdpList(array $services, EntityConnectionCollection $collection): void
    {
        foreach ($services as $service) {
            $institutionId = $service->getInstitutionId();
            if ($institutionId === null) {
                continue;
            }
            $collection->addIdpList($this->listTestIdps(new InstitutionId($institutionId)));
        }
    }
}
