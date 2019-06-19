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
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Provider\EntityQueryRepositoryProvider;
use Surfnet\ServiceProviderDashboard\Application\ViewObject;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage\Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;
use Symfony\Component\Routing\RouterInterface;
use Webmozart\Assert\Assert;

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
     * @var Config
     */
    private $testManageConfig;

    /**
     * @var Config
     */
    private $prodManageConfig;

    public function __construct(
        EntityQueryRepositoryProvider $entityQueryRepositoryProvider,
        TicketServiceInterface $ticketService,
        Config $testConfig,
        Config $productionConfig,
        RouterInterface $router,
        LoggerInterface $logger,
        $oidcPlaygroundUriTest,
        $oidcPlaygroundUriProd
    ) {
        Assert::stringNotEmpty($oidcPlaygroundUriTest, 'Please set "playground_uri_test" in parameters.yml');
        Assert::stringNotEmpty($oidcPlaygroundUriProd, 'Please set "playground_uri_prod" in parameters.yml');

        $this->queryRepositoryProvider = $entityQueryRepositoryProvider;
        $this->ticketService = $ticketService;
        $this->router = $router;
        $this->logger = $logger;
        $this->oidcPlaygroundUriTest = $oidcPlaygroundUriTest;
        $this->oidcPlaygroundUriProd = $oidcPlaygroundUriProd;
        $this->testManageConfig = $testConfig;
        $this->prodManageConfig = $productionConfig;
    }

    public function createEntityUuid()
    {
        return (string)Uuid::uuid1();
    }

    public function getEntityById($id)
    {
        return $this->queryRepositoryProvider->getEntityRepository()->findById($id);
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
                $entity = $this->queryRepositoryProvider
                    ->getManageProductionQueryClient()
                    ->findByManageId($id);

                // Entities that are still excluded from push are not realy published, but have a publication request
                // with the service desk.
                if ($entity->getMetaData()->getCoin()->getExcludeFromPush()) {
                    $entity->updateStatus(Entity::STATE_PUBLICATION_REQUESTED);
                }

                $issue = $this->findIssueBy($entity);
                if ($issue) {
                    $this->updateEntityStatusWithJiraTicketStatus($entity, $issue);
                }

                return Entity::fromManageResponse($entity, $manageTarget, $service, $this->oidcPlaygroundUriTest, $this->oidcPlaygroundUriProd);
                break;
            case 'test':
                $entity = $this->queryRepositoryProvider
                    ->getManageTestQueryClient()
                    ->findByManageId($id);

                return Entity::fromManageResponse($entity, $manageTarget, $service, $this->oidcPlaygroundUriTest, $this->oidcPlaygroundUriProd);
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

        $testEntities = $this->findPublishedTestEntitiesByTeamName(
            $service->getTeamName(),
            $this->testManageConfig->getPublicationStatus()->getStatus()
        );
        foreach ($testEntities as $result) {
            $entities[] = ViewObject\Entity::fromManageTestResult($result, $this->router, $service->getId());
        }

        $productionEntities = $this->findPublishedProductionEntitiesByTeamName(
            $service->getTeamName(),
            $this->prodManageConfig->getPublicationStatus()->getStatus()
        );
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
     * @param $serivceid
     * @return Entity[]
     */
    private function findDraftEntitiesByServiceId($serivceid)
    {
        return $this->queryRepositoryProvider
            ->getEntityRepository()
            ->findByServiceId($serivceid);
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
     * @param $teamName
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
                    if ($issueCollection->getIssueByKey($entity->getId())) {
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
     * @param $entity
     * @return Issue|null
     */
    private function findIssueBy($entity)
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
