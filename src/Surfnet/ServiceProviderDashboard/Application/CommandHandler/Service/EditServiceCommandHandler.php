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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Service;

use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class EditServiceCommandHandler implements CommandHandler
{
    /**
     * @var ServiceRepository
     */
    private $repository;

    /**
     * @param ServiceRepository $repository
     */
    public function __construct(ServiceRepository $repository)
    {
        $this->repository = $repository;
    }

    public function handle(EditServiceCommand $command)
    {
        $service = $this->repository->findById($command->getId());

        if (is_null($service)) {
            throw new EntityNotFoundException('The requested Service cannot be found');
        }

        $service->setSupplier($command->getSupplier());
        $service->setArchived($command->isArchived());
        $service->setEnvironment($command->getEnvironment());
        $service->setStatus($command->getStatus());
        $service->setJanusId($command->getJanusId());
        $service->setImportUrl($command->getImportUrl());
        $service->setMetadataUrl($command->getMetadataUrl());
        $service->setMetadataXml($command->getMetadataXml());
        $service->setAcsLocation($command->getAcsLocation());
        $service->setEntityId($command->getEntityId());
        $service->setCertificate($command->getCertificate());
        $service->setLogoUrl($command->getLogoUrl());
        $service->setNameNl($command->getNameNl());
        $service->setNameEn($command->getNameEn());
        $service->setDescriptionNl($command->getDescriptionNl());
        $service->setDescriptionEn($command->getDescriptionEn());
        $service->setApplicationUrl($command->getApplicationUrl());
        $service->setEulaUrl($command->getEulaUrl());
        $service->setAdministrativeContact($command->getAdministrativeContact());
        $service->setTechnicalContact($command->getTechnicalContact());
        $service->setSupportContact($command->getSupportContact());
        $service->setGivenNameAttribute($command->getGivenNameAttribute());
        $service->setSurNameAttribute($command->getSurNameAttribute());
        $service->setCommonNameAttribute($command->getCommonNameAttribute());
        $service->setDisplayNameAttribute($command->getDisplayNameAttribute());
        $service->setEmailAddressAttribute($command->getEmailAddressAttribute());
        $service->setOrganizationAttribute($command->getOrganizationAttribute());
        $service->setOrganizationTypeAttribute($command->getOrganizationTypeAttribute());
        $service->setAffiliationAttribute($command->getAffiliationAttribute());
        $service->setEntitlementAttribute($command->getEntitlementAttribute());
        $service->setPrincipleNameAttribute($command->getPrincipleNameAttribute());
        $service->setUidAttribute($command->getUidAttribute());
        $service->setPreferredLanguageAttribute($command->getPreferredLanguageAttribute());
        $service->setPersonalCodeAttribute($command->getPersonalCodeAttribute());
        $service->setScopedAffiliationAttribute($command->getScopedAffiliationAttribute());
        $service->setEduPersonTargetedIDAttribute($command->getEduPersonTargetedIDAttribute());
        $service->setComments($command->getComments());

        $this->repository->save($service);
    }
}
