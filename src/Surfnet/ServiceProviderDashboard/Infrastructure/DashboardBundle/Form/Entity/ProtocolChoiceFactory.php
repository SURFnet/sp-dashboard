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

use Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage\Config;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Service\OidcngEnabledMarshaller;

class ProtocolChoiceFactory
{
    /**
     * @var OidcngEnabledMarshaller
     */
    private $oidcngEnabledMarshaller;

    /**
     * @var Config[] $manageConfig
     */
    private $manageConfig;

    /**
     * @var Service
     */
    private $service;

    private $availableOptions = [
        Entity::TYPE_SAML => 'entity.type.saml20.title',
        Entity::TYPE_OPENID_CONNECT => 'entity.type.oidc.title',
        Entity::TYPE_OPENID_CONNECT_TNG => 'entity.type.oidcng.client.title',
        Entity::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER => 'entity.type.oidcng.resource_server.title',
    ];

    public function __construct(Config $manageConfigTest, Config $manageConfigProd)
    {
        $this->manageConfig = [
            Entity::ENVIRONMENT_TEST => $manageConfigTest,
            Entity::ENVIRONMENT_PRODUCTION => $manageConfigProd,
        ];

        $this->oidcngEnabledMarshaller = new OidcngEnabledMarshaller();
    }

    public function setService(Service $service)
    {
        $this->service = $service;
    }

    /**
     * Based on target environment, builds the available protocol choices for the ChooseEntityType form.
     * @param $targetEnvironment
     * @return array
     */
    public function buildOptions($targetEnvironment)
    {
        $manageConfig = $this->manageConfig[$targetEnvironment];
        $options = $this->availableOptions;
        if (!$this->oidcngEnabledMarshaller->allowed($this->service, $manageConfig->getOidcngEnabled()->isEnabled())) {
            unset($options[Entity::TYPE_OPENID_CONNECT_TNG]);
            unset($options[Entity::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER]);
        }
        return array_flip($options);
    }
}
