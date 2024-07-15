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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Entity;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateEntityAclCommand implements Command
{
    public function __construct(
        private readonly ManageEntity $manageEntity,
        /**
         * @var                                                                                 IdentityProvider[]
         */
        #[Assert\All([
            new Assert\NotBlank(),
            new Assert\Type(type: IdentityProvider::class),
        ])]
        private array $selected,
        private bool $selectAll,
        private readonly Contact $applicant,
    ) {
    }

    public function getManageEntity(): ManageEntity
    {
        return $this->manageEntity;
    }

    /**
     * @return IdentityProvider[]
     */
    public function getSelected(): array
    {
        return $this->selected;
    }

    /**
     * @param IdentityProvider[] $idps
     */
    public function setSelected(array $idps): void
    {
        $this->selected = $idps;
    }

    public function isSelectAll(): bool
    {
        return $this->selectAll;
    }

    /**
     * @param bool $selectAll
     */
    public function setSelectAll($selectAll): void
    {
        $this->selectAll = (bool)$selectAll;
    }

    public function getApplicant(): Contact
    {
        return $this->applicant;
    }
}
