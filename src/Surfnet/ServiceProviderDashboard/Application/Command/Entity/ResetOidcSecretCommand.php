<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Entity;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints as SpDashboardAssert;

class ResetOidcSecretCommand implements Command
{
    /**
     * @var ManageEntity
     */
    private $manageEntity;

    public function __construct(ManageEntity $manageEntity)
    {
        $this->manageEntity = $manageEntity;
    }

    public function getManageEntity()
    {
        return $this->manageEntity;
    }
}
