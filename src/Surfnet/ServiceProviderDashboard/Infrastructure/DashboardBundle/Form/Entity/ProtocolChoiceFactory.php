<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;

class ProtocolChoiceFactory
{
    private array $availableOptions = [
        Constants::TYPE_SAML => 'entity.type.saml20.title',
        Constants::TYPE_OPENID_CONNECT_TNG => 'entity.type.oidcng.client.title',
        Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER => 'entity.type.oidcng.resource_server.title',
        Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT => 'entity.type.oauth20.ccc.title',
    ];

    /**
     * Based on target environment, builds the available protocol choices for the ChooseEntityType form.
     */
    public function buildOptions(): array
    {
        $options = $this->availableOptions;

        return array_flip($options);
    }
}
