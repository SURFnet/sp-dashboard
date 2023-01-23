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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Exception;
use Psr\Log\LoggerInterface;
use Ramsey\Uuid\Uuid;
use Surfnet\ServiceProviderDashboard\Application\Dto\EntityDto;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Provider\EntityQueryRepositoryProvider;
use Surfnet\ServiceProviderDashboard\Application\ViewObject;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Apis\ApiConfig;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException;
use Symfony\Component\Routing\RouterInterface;
use function sprintf;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityService implements EntityServiceInterface
{
    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private EntityQueryRepositoryProvider $queryRepositoryProvider,
        private readonly TicketServiceInterface $ticketService,
        private readonly ServiceService $serviceService,
        private readonly ChangeRequestService $changeRequestService,
        private readonly ApiConfig $testManageConfig,
        private readonly ApiConfig $prodManageConfig,
        private readonly RouterInterface $router,
        private readonly LoggerInterface $logger,
        private readonly string $removalStatus
    ) {
    }

    public function createEntityUuid()
    {
        return Uuid::uuid1()->toString();
    }

    /**
     * @throws EntityNotFoundException
     */
    public function getEntityByIdAndTarget(string $id, string $manageTarget, Service $service): ManageEntity
    {
        switch ($manageTarget) {
            case 'production':
                $entity = $this->findAndverifyAccessAllowed($id, $manageTarget, $service);
                $entity->setEnvironment($manageTarget);
                $entity->setService($service);
                // Entities that are still excluded from push are not really published, but have a publication request
                // with the service desk.
                $this->updateStatus($entity);
                $this->updateOrganizationNames(
                    $entity,
                    $service->getOrganizationNameEn(),
                    $service->getOrganizationNameNl()
                );
                $issue = $this->findIssueBy($entity);
                $shouldUseTicketStatus = $entity->getStatus() !== Constants::STATE_PUBLISHED &&
                    $entity->getStatus() !== Constants::STATE_PUBLICATION_REQUESTED;
                if ($issue && $shouldUseTicketStatus) {
                    $entity->updateStatus(Constants::STATE_REMOVAL_REQUESTED);
                }
                return $entity;
            case 'test':
                $entity = $this->findAndverifyAccessAllowed($id, $manageTarget, $service);
                $entity->setEnvironment($manageTarget);
                $entity->setService($service);
                $this->updateOrganizationNames(
                    $entity,
                    $service->getOrganizationNameEn(),
                    $service->getOrganizationNameNl()
                );
                return $entity;
            default:
                throw new EntityNotFoundException(
                    sprintf(
                        'Unable to find ManageEntity for environment "%s"',
                        $manageTarget
                    )
                );
        }
    }

    private function findAndverifyAccessAllowed(string $id, string $environment, Service $service): ManageEntity
    {

        $entity = $this->queryRepositoryProvider
            ->fromEnvironment($environment)
            ->findByManageId($id);
        if ($entity === null) {
            throw new EntityNotFoundException(
                sprintf(
                    'Unable to find ManageEntity for environment "%s"',
                    $environment
                )
            );
        }

        // Allow actions on Resource Servers (viewing them outside of our team)
        if ($entity->getProtocol()->getProtocol() !== Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER) {
            $serviceFromEntity = $this->serviceService->getServiceByTeamName(
                $entity->getMetaData()->getCoin()->getServiceTeamId()
            );
            if ($serviceFromEntity->getId() !== $service->getId()) {
                throw new InvalidArgumentException(
                    'The service from the entity did not match the service passed to this method.'
                );
            }
        }

        return $entity;
    }

    public function getEntityListForService(Service $service)
    {
        $entities = [];

        $testEntities = $this->findPublishedTestEntitiesByTeamName($service->getTeamName());
        foreach ($testEntities as $result) {
            $entities[] = ViewObject\Entity::fromManageTestResult($result, $this->router, $service->getId());
        }

        $productionEntities = $this->findPublishedProductionEntitiesByTeamName($service->getTeamName());
        foreach ($productionEntities as $result) {
            $hasChangeRequest = $this->hasChangeRequests($result);
            $entities[] = ViewObject\Entity::fromManageProductionResult(
                $result,
                $this->router,
                $service->getId(),
                $hasChangeRequest
            );
        }

        return new ViewObject\EntityList($entities);
    }

    public function getEntitiesForService(Service $service)
    {
        $entities = [];

        $testEntities = $this->findPublishedTestEntitiesByTeamName($service->getTeamName());
        foreach ($testEntities as $result) {
            $entities[] = EntityDto::fromManageTestResult($result);
        }

        $productionEntities = $this->findPublishedProductionEntitiesByTeamName($service->getTeamName());
        foreach ($productionEntities as $result) {
            $entities[] = EntityDto::fromManageProductionResult($result);
        }

        return $entities;
    }

    /**
     *
     * @param string $manageId
     * @param string $env
     *
     * @return ManageEntity|null
     *
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    public function getManageEntityById($manageId, $env = 'test')
    {
        $entity = $this->queryRepositoryProvider
            ->fromEnvironment($env)
            ->findByManageId($manageId);
        $entity->setEnvironment($env);
        // Set the service associated to the entity on the entity.
        $service = $this->serviceService->getServiceByTeamName($entity->getMetaData()->getCoin()->getServiceTeamId());
        $entity->setService($service);
        $this->updateStatus($entity);
        // As the organization names are tracked on the Service, we update it on the Manage Entity Organization VO
        $this->updateOrganizationNames($entity, $service->getOrganizationNameEn(), $service->getOrganizationNameNl());
        return $entity;
    }

    /**
     * @desc get a pure manage entity together with the associated service. Notice that meta data of the
     * organization is untouched, so that any difference on the organization data can be noticed and updated from
     * the service accordingly.
     *
     * @param string $manageId
     * @param string $env
     *
     * @return ManageEntity|null
     *
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    public function getPristineManageEntityById($manageId, $env = 'test')
    {
        $entity = $this->queryRepositoryProvider
            ->fromEnvironment($env)
            ->findByManageId($manageId);
        $entity->setEnvironment($env);
        // Set the service associated to the entity on the entity.
        $service = $this->serviceService->getServiceByTeamName($entity->getMetaData()->getCoin()->getServiceTeamId());
        $entity->setService($service);
        $this->updateStatus($entity);
        return $entity;
    }

    /**
     * @param string $teamName
     * @return array|null
     * @throws QueryServiceProviderException
     */
    private function findPublishedTestEntitiesByTeamName($teamName)
    {
        return $this->queryRepositoryProvider
            ->getManageTestQueryClient()
            ->findByTeamName($teamName, $this->testManageConfig->getPublicationStatus()->getStatus());
    }

    /**
     * Find a collection of published manage entities
     *
     * - Finds published entities in Manage (from the 'production' client)
     * - Tries to match Jira issues that mention one of the manage entity id's
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param string $teamName
     * @return array|null
     * @throws QueryServiceProviderException
     */
    private function findPublishedProductionEntitiesByTeamName($teamName)
    {
        $entities = $this->queryRepositoryProvider
            ->getManageProductionQueryClient()
            ->findByTeamName($teamName, $this->prodManageConfig->getPublicationStatus()->getStatus());

        // Try to find the tickets in Jira that match the manageIds. If Jira is down or otherwise unavailable, the
        // entities are returned without updating their status. This might result in a 're request for delete'
        try {
            // Extract the Manage entity id's
            $manageIds = [];
            foreach ($entities as $entity) {
                $this->updateStatus($entity);
                $manageIds[] = $entity->getId();
            }
            $issueCollection = $this->ticketService->findByManageIds($manageIds);
            // Update the entity status to STATE_REMOVAL_REQUESTED if the Jira ticket matches one of the published
            // entities
            if (count($issueCollection) > 0) {
                foreach ($entities as $entity) {
                    $this->updateStatus($entity);
                    $issue = $issueCollection->getIssueById($entity->getId());
                    if ($issue && !$entity->isExcludedFromPush() && $issue->getIssueType() !== $this->removalStatus) {
                        // A published entity needs no status update unless it's a removal requested entity
                        continue;
                    }
                    if ($issue && $issue->getIssueType() === $this->removalStatus && !$issue->isClosedOrResolved()) {
                        $entity->updateStatus(Constants::STATE_REMOVAL_REQUESTED);
                    }
                }
            }
        } catch (Exception $e) {
            $this->logger->warning(
                'Unable to find Jira issue to monitor the status of published tickets with a remove request.',
                [$e->getMessage()]
            );
        }

        return $entities;
    }

    /**
     * @param ManageEntity $entity
     * @return Issue|null
     */
    private function findIssueBy(ManageEntity $entity)
    {
        try {
            return $this->ticketService->findByManageId($entity->getId());
        } catch (Exception $e) {
            $this->logger->warning(
                sprintf(
                    'Unable to find Jira issue with manage id "%s" to monitor the status of published tickets with a
                    remove request.',
                    $entity->getId()
                ),
                [$e->getMessage()]
            );
        }
        return null;
    }

    private function updateStatus(ManageEntity $entity)
    {
        $excludeFromPush = $entity->getMetaData()->getCoin()->getExcludeFromPush();
        if ($excludeFromPush === '1') {
            $entity->updateStatus(Constants::STATE_PUBLICATION_REQUESTED);
        }
        if ($excludeFromPush === '0') {
            $entity->updateStatus(Constants::STATE_PUBLISHED);
        }
    }

    /**
     * As the organization names are tracked on the Service, we update it on the Manage
     * Entity Organization
     */
    public function updateOrganizationNames(ManageEntity $entity, $orgNameEn, $orgNameNl)
    {
        $entity->getMetaData()->getOrganization()->updateNameEn($orgNameEn);
        $entity->getMetaData()->getOrganization()->updateNameNl($orgNameNl);
    }

    /**
     * @param ManageEntity $service
     * @return bool
     */
    private function hasChangeRequests(ManageEntity $entity): bool
    {
        $changes = $this->changeRequestService->findByIdAndProtocol(
            $entity->getId(),
            $entity->getProtocol()
        );

        return count($changes->getChangeRequests()) > 0;
    }
}
