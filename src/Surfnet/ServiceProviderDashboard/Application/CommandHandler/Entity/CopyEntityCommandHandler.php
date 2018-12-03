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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CopyEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient as ManageClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\AttributeList;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\Coin;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;

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
    private $manageTestClient;

    /**
     * @var ManageClient
     */
    private $manageProductionClient;

    /**
     * @var AttributesMetadataRepository
     */
    private $attributeMetadataRepository;

    /**
     * @param CommandBus $commandBus
     * @param EntityRepository $entityRepository
     * @param ManageClient $manageTestClient
     * @param ManageClient $manageProductionClient
     * @param AttributesMetadataRepository $attributeMetadataRepository
     */
    public function __construct(
        CommandBus $commandBus,
        EntityRepository $entityRepository,
        ManageClient $manageTestClient,
        ManageClient $manageProductionClient,
        AttributesMetadataRepository $attributeMetadataRepository
    ) {
        $this->commandBus = $commandBus;
        $this->entityRepository = $entityRepository;
        $this->manageTestClient = $manageTestClient;
        $this->manageProductionClient = $manageProductionClient;
        $this->attributeMetadataRepository = $attributeMetadataRepository;
    }

    /**
     * @param CopyEntityCommand $command
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) - The different copy actions should be broken into different
     *                                                 commands.
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    public function handle(CopyEntityCommand $command)
    {
        $dashboardId = $command->getDashboardId();
        $manageId = $command->getManageId();
        $saveEntityCommand = $command->getSaveEntityCommand();

        if (!$this->entityRepository->isUnique($dashboardId)) {
            throw new InvalidArgumentException(
                'The id that was generated for the entity was not unique, please try again'
            );
        }

        $manageClient = $this->manageProductionClient;
        if ($command->getSourceEnvironment() == 'test') {
            $manageClient = $this->manageTestClient;
        }

        $manageEntity = $manageClient->findByManageId($manageId);

        if (empty($manageEntity)) {
            throw new InvalidArgumentException(
                'Could not find entity in manage: '.$manageId
            );
        }

        $manageTeamName = $manageEntity->getMetaData()->getCoin()->getServiceTeamId();

        $manageStagingState = $this->getManageStagingState($manageEntity->getMetaData()->getCoin());

        if ($manageTeamName !== $command->getService()->getTeamName()) {
            throw new InvalidArgumentException(
                sprintf(
                    'The entity you are about to copy does not belong to the selected team: %s != %s',
                    $manageTeamName,
                    $command->getService()->getTeamName()
                )
            );
        }

        $saveEntityCommand->setStatus(Entity::STATE_PUBLISHED);
        $saveEntityCommand->setId($dashboardId);
        $saveEntityCommand->setService($command->getService());
        $saveEntityCommand->setManageId($command->getManageId());

        // Published production entities must be cloned, not copied
        $isProductionClone = $command->getEnvironment() == 'production' && $manageStagingState === 0;
        // Entities copied from test to prod should not have a manage id either
        $isCopyToProduction = $command->getEnvironment() == 'production' && $command->getSourceEnvironment() == 'test';
        if ($isProductionClone || $isCopyToProduction) {
            $saveEntityCommand->setManageId(null);
        }

        $this->commandBus->handle(
            new LoadMetadataCommand(
                $saveEntityCommand,
                ['metadata' => ['pastedMetadata' => $manageClient->getMetadataXmlByManageId($manageId)]]
            )
        );

        $this->setManageMetadataOn($saveEntityCommand, $manageEntity);

        if (!empty($manageEntity->getMetaData()->getMetaDataUrl())) {
            $saveEntityCommand->setMetadataUrl($manageEntity->getMetaData()->getMetaDataUrl());
        }

        $this->setAttributesOn($saveEntityCommand, $manageEntity->getAttributes());

        // Set the target environment
        $saveEntityCommand->setEnvironment($command->getEnvironment());
    }

    /**
     * Determine the staging state
     *
     * The state is based on the presence of the coin:exclude_from_push attribute.
     *
     * 0 means this is a production entity.
     * 1 means the entity is still in staging (access was requested).
     *
     * @param Coin $coin
     * @return int
     */
    private function getManageStagingState(Coin $coin)
    {
        if ($coin->getExcludeFromPush() === 1) {
            return 1;
        }
        return 0;
    }

    private function setManageMetadataOn(SaveSamlEntityCommand $saveEntityCommand, ManageEntity $manageMetadata)
    {
        if (!empty($manageMetadata->getMetaData()->getCoin()->getApplicationUrl())) {
            $saveEntityCommand->setApplicationUrl($manageMetadata->getMetaData()->getCoin()->getApplicationUrl());
        }

        if (!empty($manageMetadata->getMetaData()->getCoin()->getEula())) {
            $saveEntityCommand->setEulaUrl($manageMetadata->getMetaData()->getCoin()->getEula());
        }

        if (!empty($manageMetadata->getMetaData()->getCoin()->getOriginalMetadataUrl())) {
            $saveEntityCommand->setImportUrl($manageMetadata->getMetaData()->getCoin()->getOriginalMetadataUrl());
        }
    }

    private function setAttributesOn($saveEntityCommand, AttributeList $attributeList)
    {
        // Copy the ARP attributes to the new entity based on the data from manage.
        foreach ($this->attributeMetadataRepository->findAll() as $attributeDefinition) {
            $urn = reset($attributeDefinition->urns);
            $manageAttribute = $attributeList->findByUrn($urn);
            if (!$manageAttribute) {
                continue;
            }

            $setter = $attributeDefinition->setterName;
            if (empty($setter)) {
                continue;
            }

            $attribute = new Attribute();
            $attribute->setRequested(true);
            $attribute->setMotivation($manageAttribute->getMotivation());

            $saveEntityCommand->{$setter}($attribute);
        }
    }
}
