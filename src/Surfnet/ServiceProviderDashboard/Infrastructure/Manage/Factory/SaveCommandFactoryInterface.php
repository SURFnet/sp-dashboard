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

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOauthClientCredentialClientCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

interface SaveCommandFactoryInterface
{
    public function buildSamlCommandByManageEntity(
        ManageEntity $manageEntity,
        string $environment,
    ): SaveSamlEntityCommand;

    public function buildOidcngCommandByManageEntity(
        ManageEntity $manageEntity,
        string $environment,
        bool $isCopy = false,
    ): SaveOidcngEntityCommand;

    public function buildOauthCccCommandByManageEntity(
        ManageEntity $manageEntity,
        string $environment,
        bool $isCopy = false,
    ): SaveOauthClientCredentialClientCommand;

    public function buildOidcngRsCommandByManageEntity(
        ManageEntity $manageEntity,
        string $environment,
        bool $isCopy = false,
    ): SaveOidcngResourceServerEntityCommand;
}
