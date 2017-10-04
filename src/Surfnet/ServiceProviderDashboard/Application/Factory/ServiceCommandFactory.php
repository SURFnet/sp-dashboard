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

namespace Surfnet\ServiceProviderDashboard\Application\Factory;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Application\Command\Service\EditServiceCommand;

class ServiceCommandFactory
{
    public function build(Service $service)
    {
        return new EditServiceCommand(
            $service->getId(),
            $service->getSupplier(),
            $service->getTicketNumber(),
            $service->isArchived(),
            $service->getEnvironment(),
            $service->getStatus(),
            $service->getJanusId(),
            $service->getImportUrl(),
            $service->getMetadataUrl(),
            $service->getMetadataXml(),
            $service->getAcsLocation(),
            $service->getEntityId(),
            $service->getCertificate(),
            $service->getLogoUrl(),
            $service->getNameNl(),
            $service->getNameEn(),
            $service->getDescriptionNl(),
            $service->getDescriptionEn(),
            $service->getApplicationUrl(),
            $service->getEulaUrl(),
            $service->getAdministrativeContact(),
            $service->getTechnicalContact(),
            $service->getSupportContact(),
            $service->getGivenNameAttribute(),
            $service->getSurNameAttribute(),
            $service->getCommonNameAttribute(),
            $service->getDisplayNameAttribute(),
            $service->getEmailAddressAttribute(),
            $service->getOrganizationAttribute(),
            $service->getOrganizationTypeAttribute(),
            $service->getAffiliationAttribute(),
            $service->getEntitlementAttribute(),
            $service->getPrincipleNameAttribute(),
            $service->getUidAttribute(),
            $service->getPreferredLanguageAttribute(),
            $service->getPersonalCodeAttribute(),
            $service->getScopedAffiliationAttribute(),
            $service->getEduPersonTargetedIDAttribute(),
            $service->getComments()
        );
    }
}
