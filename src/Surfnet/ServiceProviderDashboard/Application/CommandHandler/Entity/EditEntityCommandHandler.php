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

use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\EditEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;

class EditEntityCommandHandler implements CommandHandler
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

    public function handle(EditEntityCommand $command)
    {
        $entity = $this->repository->findById($command->getId());

        if (is_null($entity)) {
            throw new EntityNotFoundException('The requested Service cannot be found');
        }

        $entity->setService($command->getService());
        $entity->setArchived($command->isArchived());
        $entity->setEnvironment($command->getEnvironment());
        $entity->setStatus($command->getStatus());
        $entity->setJanusId($command->getJanusId());
        $entity->setImportUrl($command->getImportUrl());
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

        $this->repository->save($entity);
    }
}
