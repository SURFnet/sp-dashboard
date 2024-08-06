<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

class ContractualBaseService
{
    public function writeContractualBase(ManageEntity $entity): void
    {
        // 1. The entity must be targeted at the production env
        if ($entity->getEnvironment() !== Constants::ENVIRONMENT_PRODUCTION) {
            return;
        }

        // 2. It must be a SAML or OIDC entity
        $protocol = $entity->getProtocol()->getProtocol();
        if (!in_array($protocol, [Constants::TYPE_SAML, Constants::TYPE_OPENID_CONNECT_TNG], true)) {
            return;
        }

        // 3. Determine the contractual base, based on the service type
        $serviceType = $entity->getService()->getServiceType();
        $contractualBase = match ($serviceType) {
            Constants::SERVICE_TYPE_INSTITUTE => Constants::CONTRACTUAL_BASE_IX,
            Constants::SERVICE_TYPE_NON_INSTITUTE => Constants::CONTRACTUAL_BASE_AO,
            default => null,
        };

        if ($contractualBase === null) {
            return;
        }

        // 4. Set the coin value on the entity
        $entity->getMetaData()?->getCoin()->setContractualBase($contractualBase);
    }
}
