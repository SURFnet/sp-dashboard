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
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Symfony\Component\Validator\Constraints as Assert;

class UpdateEntityAclCommand implements Command
{
    private $manageEntity;

    /**
     * @var IdentityProvider[]
     * @Assert\All({
     *     @Assert\NotBlank(),
     *     @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider")
     * })
     */
    private $selected;

    /**
     * @var bool
     */
    private $selectAll = false;

    public function __construct(ManageEntity $entity, array $selectedIdps, bool $selectAll)
    {
        $this->manageEntity = $entity;
        $this->selected = $selectedIdps;
        $this->selectAll = $selectAll;
    }

    /**
     * @return string
     */
    public function getManageEntity()
    {
        return $this->manageEntity;
    }

    /**
     * @return IdentityProvider[]
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * @param IdentityProvider[] $idps
     */
    public function setSelected(array $idps)
    {
        $this->selected = $idps;
    }

    /**
     * @return bool
     */
    public function isSelectAll()
    {
        return $this->selectAll;
    }

    /**
     * @param bool $selectAll
     */
    public function setSelectAll($selectAll)
    {
        $this->selectAll = (bool)$selectAll;
    }
}
