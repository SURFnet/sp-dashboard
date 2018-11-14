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
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
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
     * @throws InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        if ($command->getEnvironment() === 'test') {
            $manageClient = $this->manageTestClient;
        }

        $manageEntity = $manageClient->findByManageId($manageId);

        if (empty($manageEntity)) {
            throw new InvalidArgumentException(
                'Could not find entity in manage: '.$manageId
            );
        }

        $manageMetadata = $manageEntity['data']['metaDataFields'];
        $manageTeamName = $manageMetadata['coin:service_team_id'];
        $arp = isset($manageEntity['data']['arp']['attributes']) ? $manageEntity['data']['arp']['attributes'] : [];
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

        $this->commandBus->handle(
            new LoadMetadataCommand(
                $saveEntityCommand,
                ['metadata' => ['pastedMetadata' => $manageClient->getMetadataXmlByManageId($manageId)]]
            )
        );

        if (isset($manageMetadata['coin:application_url'])) {
            $saveEntityCommand->setApplicationUrl($manageMetadata['coin:application_url']);
        }

        if (isset($manageMetadata['coin:eula'])) {
            $saveEntityCommand->setEulaUrl($manageMetadata['coin:eula']);
        }

        if (isset($manageMetadata['coin:original_metadata_url'])) {
            $saveEntityCommand->setImportUrl($manageMetadata['coin:original_metadata_url']);
        }

        if (isset($manageEntity['data']['metadataurl'])) {
            $saveEntityCommand->setMetadataUrl($manageEntity['data']['metadataurl']);
        }

        // Copy the ARP attributes to the new entity based on the data from manage.
        foreach ($this->attributeMetadataRepository->findAll() as $attributeDefinition) {
            $urn = reset($attributeDefinition->urns);

            if (!isset($arp[$urn])) {
                continue;
            }

            $setter = $attributeDefinition->setterName;
            if (empty($setter)) {
                continue;
            }

            $motivation = isset($arp[$urn][0]['motivation']) ? $arp[$urn][0]['motivation'] : false;

            $attribute = new Attribute();
            $attribute->setRequested(true);

            if ($motivation) {
                $attribute->setMotivation($motivation);
            }

            $saveEntityCommand->{$setter}($attribute);
        }

        // Set the target environment
        $saveEntityCommand->setEnvironment($command->getEnvironment());
    }
}
