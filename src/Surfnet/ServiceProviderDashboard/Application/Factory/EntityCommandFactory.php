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

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\EditEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

class EntityCommandFactory
{
    public function build(Entity $entity)
    {
        return new EditEntityCommand(
            $entity->getId(),
            $entity->getService(),
            $entity->getTicketNumber(),
            $entity->isArchived(),
            $entity->getEnvironment(),
            $entity->getImportUrl(),
            $entity->getPastedMetadata(),
            $entity->getMetadataUrl(),
            $entity->getAcsLocation(),
            $entity->getEntityId(),
            $entity->getCertificate(),
            $entity->getLogoUrl(),
            $entity->getNameNl(),
            $entity->getNameEn(),
            $entity->getDescriptionNl(),
            $entity->getDescriptionEn(),
            $entity->getApplicationUrl(),
            $entity->getEulaUrl(),
            $entity->getAdministrativeContact(),
            $entity->getTechnicalContact(),
            $entity->getSupportContact(),
            $entity->getGivenNameAttribute(),
            $entity->getSurNameAttribute(),
            $entity->getCommonNameAttribute(),
            $entity->getDisplayNameAttribute(),
            $entity->getEmailAddressAttribute(),
            $entity->getOrganizationAttribute(),
            $entity->getOrganizationTypeAttribute(),
            $entity->getAffiliationAttribute(),
            $entity->getEntitlementAttribute(),
            $entity->getPrincipleNameAttribute(),
            $entity->getUidAttribute(),
            $entity->getPreferredLanguageAttribute(),
            $entity->getPersonalCodeAttribute(),
            $entity->getScopedAffiliationAttribute(),
            $entity->getEduPersonTargetedIDAttribute(),
            $entity->getComments()
        );
    }
}
