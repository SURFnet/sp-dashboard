<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Factory;

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\AttributeList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class SaveCommandFactory implements SaveCommandFactoryInterface
{
    /**
     * @var AttributesMetadataRepository
     */
    private $attributeRepository;

    public function __construct(AttributesMetadataRepository $attributeRepository)
    {
        $this->attributeRepository = $attributeRepository;
    }

    public function buildSamlCommandByManageEntity(ManageEntity $manageEntity, string $environment): SaveSamlEntityCommand
    {
        $command = new SaveSamlEntityCommand();
        $metaData = $manageEntity->getMetaData();
        $coins = $manageEntity->getMetaData()->getCoin();

        $command->setId($manageEntity->getId());
        $command->setStatus($manageEntity->getStatus());
        $command->setEnvironment($environment);
        $command->setMetadataUrl($metaData->getMetaDataUrl());
        $command->setAcsLocation($metaData->getAcsLocation());
        $command->setEntityId($metaData->getEntityId());
        $command->setCertificate($metaData->getCertData());
        $command->setLogoUrl($metaData->getLogo()->getUrl());
        $command->setAdministrativeContact(Contact::from($metaData->getContacts()->findAdministrativeContact()));
        $command->setTechnicalContact(Contact::from($metaData->getContacts()->findTechnicalContact()));
        $command->setSupportContact(Contact::from($metaData->getContacts()->findSupportContact()));
        $command->setNameIdFormat($metaData->getNameIdFormat());

        // Organization data
        $command->setNameNl($metaData->getNameNl());
        $command->setNameEn($metaData->getNameEn());
        $command->setDescriptionNl($metaData->getDescriptionNl());
        $command->setDescriptionEn($metaData->getDescriptionEn());

        // Coin data
        $command->setApplicationUrl($coins->getApplicationUrl());
        $command->setEulaUrl($coins->getEula());
        $command->setImportUrl($coins->getOriginalMetadataUrl());

        // Attributes
        $this->setAttributes($command, $manageEntity->getAttributes());

        return $command;
    }

    public function buildOidcngCommandByManageEntity(ManageEntity $manageEntity, string $environment): SaveOidcngEntityCommand
    {

    }

    public function buildOidcngRsCommandByManageEntity(ManageEntity $manageEntity, string $environment): SaveOidcngResourceServerEntityCommand
    {

    }

    private function setAttributes(SaveSamlEntityCommand $command, AttributeList $attributeList)
    {
        foreach ($this->attributeRepository->findAll() as $attributeDefinition) {
            $urn = reset($attributeDefinition->urns);
            $manageAttribute = $attributeList->findByUrn($urn);
            if (!$manageAttribute) {
                continue;
            }

            $attribute = new Attribute();
            $attribute->setRequested(true);
            $attribute->setMotivation($manageAttribute->getMotivation());

            $setter = $attributeDefinition->setterName;
            $command->{$setter}($attribute);
        }
    }
}