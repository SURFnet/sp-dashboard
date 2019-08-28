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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Ramsey\Uuid\Uuid;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class SaveSamlEntityCommandHandler implements CommandHandler
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @param EntityRepository $repository
     */
    public function __construct(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param SaveSamlEntityCommand $command
     * @throws EntityNotFoundException
     * @throws InvalidArgumentException
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function handle(SaveSamlEntityCommand $command)
    {
        // If the entity does not exist yet, create it on the fly
        if (is_null($command->getId())) {
            $id = Uuid::uuid1()->toString();
            if (!$this->repository->isUnique($id)) {
                throw new InvalidArgumentException(
                    'The id that was generated for the entity was not unique, please try again'
                );
            }

            $entity = new Entity();
            $entity->setId($id);
            $entity->setService($command->getService());
            $command->setId($id);
        } else {
            $entity = $this->repository->findById($command->getId());
        }

        if (is_null($entity)) {
            throw new EntityNotFoundException('The requested Service cannot be found');
        }

        $entity->setProtocol(Entity::TYPE_SAML);
        $entity->setService($command->getService());
        $entity->setManageId($command->getManageId());
        $entity->setArchived($command->isArchived());
        $entity->setEnvironment($command->getEnvironment());
        $entity->setImportUrl($command->getImportUrl());
        $entity->setPastedMetadata($command->getPastedMetadata());
        $entity->setMetadataUrl($command->getMetadataUrl());
        $entity->setAcsLocation($command->getAcsLocation());
        $entity->setEntityId($command->getEntityId());
        $entity->setCertificate($command->getCertificate());
        $entity->setLogoUrl($command->getLogoUrl());
        $entity->setNameNl($command->getNameNl());
        $entity->setNameEn($command->getNameEn());
        $entity->setDescriptionNl($command->getDescriptionNl());
        $entity->setDescriptionEn($command->getDescriptionEn());
        $entity->setApplicationUrl($command->getApplicationUrl());
        $entity->setEulaUrl($command->getEulaUrl());
        $entity->setAdministrativeContact($command->getAdministrativeContact());
        $entity->setTechnicalContact($command->getTechnicalContact());
        $entity->setSupportContact($command->getSupportContact());
        $entity->setGivenNameAttribute($command->getGivenNameAttribute());
        $entity->setSurNameAttribute($command->getSurNameAttribute());
        $entity->setCommonNameAttribute($command->getCommonNameAttribute());
        $entity->setDisplayNameAttribute($command->getDisplayNameAttribute());
        $entity->setEmailAddressAttribute($command->getEmailAddressAttribute());
        $entity->setOrganizationAttribute($command->getOrganizationAttribute());
        $entity->setOrganizationTypeAttribute($command->getOrganizationTypeAttribute());
        $entity->setAffiliationAttribute($command->getAffiliationAttribute());
        $entity->setEntitlementAttribute($command->getEntitlementAttribute());
        $entity->setPrincipleNameAttribute($command->getPrincipleNameAttribute());
        $entity->setUidAttribute($command->getUidAttribute());
        $entity->setPreferredLanguageAttribute($command->getPreferredLanguageAttribute());
        $entity->setPersonalCodeAttribute($command->getPersonalCodeAttribute());
        $entity->setScopedAffiliationAttribute($command->getScopedAffiliationAttribute());
        $entity->setEduPersonTargetedIDAttribute($command->getEduPersonTargetedIDAttribute());
        $entity->setComments($command->getComments());

        // Set the name id format, fall back on the most sensible default (transient). This is only for users not
        // utilizing the import feature.
        if ($command->hasNameIdFormat()) {
            $entity->setNameIdFormat($command->getNameIdFormat());
        } else {
            $entity->setNameIdFormat(Entity::NAME_ID_FORMAT_TRANSIENT);
        }

        $entity->setOrganizationNameNl($command->getOrganizationNameNl());
        $entity->setOrganizationNameEn($command->getOrganizationNameEn());
        $entity->setOrganizationDisplayNameNl($command->getOrganizationDisplayNameNl());
        $entity->setOrganizationDisplayNameEn($command->getOrganizationDisplayNameEn());
        $entity->setOrganizationUrlNl($command->getOrganizationUrlNl());
        $entity->setOrganizationUrlEn($command->getOrganizationUrlEn());

        $this->repository->save($entity);
    }
}
