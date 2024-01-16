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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Entity;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Symfony\Component\Validator\Constraints as Assert;

class PublishEntityProductionCommand implements PublishProductionCommandInterface, Command
{
    private bool $isClientReset = false;

    public function __construct(
        #[Assert\Type(\Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity::class)]private readonly ManageEntity $manageEntity,
        private readonly Contact $applicant
    ) {
    }

    public function isClientReset(): bool
    {
        return $this->isClientReset;
    }

    public function markPublishClientReset(): void
    {
        $this->isClientReset = true;
    }

    public function getManageEntity(): ManageEntity
    {
        return $this->manageEntity;
    }

    public function getApplicant(): Contact
    {
        return $this->applicant;
    }
}
