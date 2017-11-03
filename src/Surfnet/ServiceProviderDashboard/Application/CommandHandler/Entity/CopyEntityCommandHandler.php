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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity;

use League\Tactician\CommandBus;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CopyEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageClient;

class CopyEntityCommandHandler implements CommandHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var EntityRepository
     */
    private $entityRepository;

    /**
     * @var ManageClient
     */
    private $manageClient;

    /**
     * @var AttributesMetadataRepository
     */
    private $attributeMetadataRepository;

    /**
     * @param CommandBus $commandBus
     * @param EntityRepository $entityRepository
     * @param ManageClient $manageClient
     * @param AttributesMetadataRepository $attributeMetadataRepository
     */
    public function __construct(
        CommandBus $commandBus,
        EntityRepository $entityRepository,
        ManageClient $manageClient,
        AttributesMetadataRepository $attributeMetadataRepository
    ) {
        $this->commandBus = $commandBus;
        $this->entityRepository = $entityRepository;
        $this->manageClient = $manageClient;
        $this->attributeMetadataRepository = $attributeMetadataRepository;
    }

    /**
     * @param CopyEntityCommand $command
     *
     * @throws InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function handle(CopyEntityCommand $command)
    {
        $dashboardId = $command->getDashboardId();
        $manageId = $command->getManageId();

        if (!$this->entityRepository->isUnique($dashboardId)) {
            throw new InvalidArgumentException(
                'The id that was generated for the entity was not unique, please try again'
            );
        }

        $manageEntity = $this->manageClient->findByManageId($manageId);
        if (empty($manageEntity)) {
            throw new InvalidArgumentException(
                'Could not find entity in manage: ' . $manageId
            );
        }

        $manageMetadata = $manageEntity['data']['metaDataFields'];
        $manageTeamName = $manageMetadata['coin:service_team_id'];

        if ($manageTeamName !== $command->getService()->getTeamName()) {
            throw new InvalidArgumentException(
                sprintf(
                    'The entity you are about to copy does not belong to the selected team: %s != %s',
                    $manageTeamName,
                    $command->getService()->getTeamName()
                )
            );
        }

        $entity = new Entity();
        $entity->setId($dashboardId);
        $entity->setService($command->getService());
        $entity->setManageId($manageId);

        $this->entityRepository->save($entity);

        $this->commandBus->handle(
            new LoadMetadataCommand(
                $dashboardId,
                '',
                $this->manageClient->getMetadataXmlByManageId($manageId)
            )
        );

        $entity = $this->entityRepository->findById($entity->getId());

        if (isset($manageMetadata['name:en'])) {
            $entity->setNameEn($manageMetadata['name:en']);
        }

        if (isset($manageMetadata['name:nl'])) {
            $entity->setNameNl($manageMetadata['name:nl']);
        }

        if (isset($manageMetadata['description:en'])) {
            $entity->setDescriptionEn($manageMetadata['description:en']);
        }

        if (isset($manageMetadata['description:nl'])) {
            $entity->setDescriptionNl($manageMetadata['description:nl']);
        }

        if (isset($manageMetadata['coin:original_metadata_url'])) {
            $entity->setImportUrl($manageMetadata['coin:original_metadata_url']);
        }

        if (isset($manageMetadata['logo:0:url'])) {
            $entity->setLogoUrl($manageMetadata['logo:0:url']);
        }

        if (isset($manageEntity['data']['metadataurl'])) {
            $entity->setMetadataUrl($manageEntity['data']['metadataurl']);
        }

        foreach ($this->attributeMetadataRepository->findAllMotivationAttributes() as $attributeDefinition) {
            $urn = reset($attributeDefinition->urns);
            if (empty($manageMetadata[$urn])) {
                continue;
            }

            $setter = $attributeDefinition->setterName;
            if (empty($setter)) {
                continue;
            }

            $attribute = new Attribute();
            $attribute->setRequested(true);
            $attribute->setMotivation($manageMetadata[$urn]);

            $entity->{$setter}($attribute);
        }

        // Set the target environment
        $entity->setEnvironment($command->getEnvironment());

        $this->entityRepository->save($entity);
    }
}
