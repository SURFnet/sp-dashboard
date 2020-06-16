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
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage\Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\ResourceServerCollection;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class EntityService implements EntityServiceInterface
{
    /**
     * @var EntityQueryRepositoryProvider
     */
    private $queryRepositoryProvider;

    /**
     * @var TicketServiceInterface
     */
    private $ticketService;

    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var string
     */
    private $oidcPlaygroundUriTest;

    /**
     * @var string
     */
    private $oidcPlaygroundUriProd;
    /**
     * @var string
     */
    private $oidcngPlaygroundUriTest;

    /**
     * @var string
     */
    private $oidcngPlaygroundUriProd;

    /**
     * @var Config
     */
    private $testManageConfig;

    /**
     * @var Config
     */
    private $prodManageConfig;

    /**
     * @param EntityQueryRepositoryProvider $entityQueryRepositoryProvider
     * @param TicketServiceInterface $ticketService
     * @param Config $testConfig
     * @param Config $productionConfig
     * @param RouterInterface $router
     * @param LoggerInterface $logger
     * @param string $oidcPlaygroundUriTest
     * @param string $oidcPlaygroundUriProd
     * @param string $oidcngPlaygroundUriTest
     * @param string $oidcngPlaygroundUriProd
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityQueryRepositoryProvider $entityQueryRepositoryProvider,
        TicketServiceInterface $ticketService,
        Config $testConfig,
        Config $productionConfig,
        RouterInterface $router,
        LoggerInterface $logger,
        $oidcPlaygroundUriTest,
        $oidcPlaygroundUriProd,
        $oidcngPlaygroundUriTest,
        $oidcngPlaygroundUriProd
    ) {
        Assert::stringNotEmpty($oidcPlaygroundUriTest, 'Please set "playground_uri_test" in parameters.yml');
        Assert::stringNotEmpty($oidcPlaygroundUriProd, 'Please set "playground_uri_prod" in parameters.yml');
        Assert::stringNotEmpty($oidcngPlaygroundUriTest, 'Please set "oidcng_playground_uri_test" in parameters.yml');
        Assert::stringNotEmpty($oidcngPlaygroundUriProd, 'Please set "oidcng_playground_uri_prod" in parameters.yml');

        $this->queryRepositoryProvider = $entityQueryRepositoryProvider;
        $this->ticketService = $ticketService;
        $this->router = $router;
        $this->logger = $logger;
        $this->oidcPlaygroundUriTest = $oidcPlaygroundUriTest;
        $this->oidcPlaygroundUriProd = $oidcPlaygroundUriProd;
        $this->oidcngPlaygroundUriTest = $oidcngPlaygroundUriTest;
        $this->oidcngPlaygroundUriProd = $oidcngPlaygroundUriProd;
        $this->testManageConfig = $testConfig;
        $this->prodManageConfig = $productionConfig;
    }

    public function createEntityUuid()
    {
        return Uuid::uuid1()->toString();
    }

    /**
     * @param int $id
     * @return Entity|null
     */
    public function getEntityById($id)
    {
        $entity = $this->queryRepositoryProvider->getEntityRepository()->findById($id);
        if ($entity && $entity->getProtocol() === Entity::TYPE_OPENID_CONNECT_TNG) {
            // Load the Possibly connected resource servers
            $resourceServers = [];
            switch ($entity->getEnvironment()) {
                case Entity::ENVIRONMENT_TEST:
                    foreach ($entity->getOidcngResourceServers()->getResourceServers() as $clientId) {
                        $resourceServers[] = $this->queryRepositoryProvider
                            ->getManageTestQueryClient()
                            ->findByEntityId($clientId, $this->testManageConfig->getPublicationStatus()->getStatus());
                    }
                    break;
                case Entity::ENVIRONMENT_PRODUCTION:
                    foreach ($entity->getOidcngResourceServers()->getResourceServers() as $clientId) {
                        $resourceServers[] = $this->queryRepositoryProvider
                            ->getManageProductionQueryClient()
                            ->findByEntityId($clientId, $this->prodManageConfig->getPublicationStatus()->getStatus());
                    }
                    break;
            }
            $entity->setOidcngResourceServers(new ResourceServerCollection($resourceServers));
        }
        return $entity;
    }

    public function findByManageId(string $manageId, string $environment): ManageEntity
    {
        switch ($environment) {
            case 'production':
                return $this->queryRepositoryProvider
                    ->getManageProductionQueryClient()
                    ->findByManageId($manageId);
                break;
            case 'test':
                return $this->queryRepositoryProvider
                    ->getManageTestQueryClient()
                    ->findByManageId($manageId);
                break;
            default:
                throw new EntityNotFoundException(
                    sprintf(
                        'Unable to find Manage entity identified by %s in environment %s',
                        $manageId,
                        $environment
                    )
                );
                break;
        }
    }

    /**
     * @param string $id
     * @param string $manageTarget
     * @param Service $service
     * @return mixed|Entity|null
     * @throws QueryServiceProviderException
     */
    public function getEntityByIdAndTarget($id, $manageTarget, Service $service)
    {
        switch ($manageTarget) {
            case 'production':
                $entity = $this->findByManageId($id, $manageTarget);
                // Entities that are still excluded from push are not realy published, but have a publication request
                // with the service desk.
                if ($entity->getMetaData()->getCoin()->getExcludeFromPush()) {
                    $entity->updateStatus(Entity::STATE_PUBLICATION_REQUESTED);
                }

                $issue = $this->findIssueBy($entity);
                if ($issue) {
                    $this->updateEntityStatusWithJiraTicketStatus($entity, $issue);
                }

                return Entity::fromManageResponse(
                    $entity,
                    $manageTarget,
                    $service,
                    $this->oidcPlaygroundUriTest,
                    $this->oidcPlaygroundUriProd,
                    $this->oidcngPlaygroundUriTest,
                    $this->oidcngPlaygroundUriProd
                );
                break;
            case 'test':
                $entity = $this->findByManageId($id, $manageTarget);
                return Entity::fromManageResponse(
                    $entity,
                    $manageTarget,
                    $service,
                    $this->oidcPlaygroundUriTest,
                    $this->oidcPlaygroundUriProd,
                    $this->oidcngPlaygroundUriTest,
                    $this->oidcngPlaygroundUriProd
                );
                break;
            default:
                return $this->getEntityById($id);
                break;
        }
    }

    public function getEntityListForService(Service $service)
    {
        $entities = [];

        $draftEntities = $this->findDraftEntitiesByServiceId($service->getId());
        foreach ($draftEntities as $entity) {
            $entities[] = ViewObject\Entity::fromEntity($entity, $this->router);
        }

        $testEntities = $this->findPublishedTestEntitiesByTeamName($service->getTeamName());
        foreach ($testEntities as $result) {
            $entities[] = ViewObject\Entity::fromManageTestResult($result, $this->router, $service->getId());
        }

        $productionEntities = $this->findPublishedProductionEntitiesByTeamName($service->getTeamName());
        foreach ($productionEntities as $result) {
            $entities[] = ViewObject\Entity::fromManageProductionResult($result, $this->router, $service->getId());
        }

        return new ViewObject\EntityList($entities);
    }

    public function getEntitiesForService(Service $service)
    {
        $entities = [];

        $draftEntities = $this->findDraftEntitiesByServiceId($service->getId());
        foreach ($draftEntities as $entity) {
            $entities[] = EntityDto::fromEntity($entity);
        }

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
        return $this->queryRepositoryProvider
            ->fromEnvironment($env)
            ->findByManageId($manageId);
    }

    /**
     * @param int $serviceId
     * @return Entity[]
     */
    private function findDraftEntitiesByServiceId($serviceId)
    {
        return $this->queryRepositoryProvider
            ->getEntityRepository()
            ->findByServiceId($serviceId);
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
                $manageIds[] = $entity->getId();
            }
            $issueCollection = $this->ticketService->findByManageIds($manageIds);
            // Update the entity status to STATE_REMOVAL_REQUESTED if the Jira ticket matches one of the published
            // entities
            if (count($issueCollection) > 0) {
                foreach ($entities as $entity) {
                    if ($issueCollection->getIssueById($entity->getId())) {
                        $entity->updateStatus(Entity::STATE_REMOVAL_REQUESTED);
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

    private function updateEntityStatusWithJiraTicketStatus(ManageEntity $entity, Issue $issue)
    {
        if ($issue instanceof Issue) {
            $entity->updateStatus(Entity::STATE_REMOVAL_REQUESTED);
        }
    }
}
