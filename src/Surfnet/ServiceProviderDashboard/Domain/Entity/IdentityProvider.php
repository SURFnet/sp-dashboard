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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact as ContactPerson;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\OidcGrantType;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\AttributeList;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;
use Symfony\Component\Validator\Constraints as Assert;

class IdentityProvider
{
    /**
     * @var string
     */
    private $manageId;
    /**
     * @var string
     */
    private $entityId;
    /**
     * @var string
     */
    private $nameNl;
    /**
     * @var string
     */
    private $nameEn;

    /**
     * @param string $manageId
     * @param string $entityId
     * @param string $nameNl
     * @param string $nameEn
     */
    public function __construct($manageId, $entityId, $nameNl, $nameEn)
    {
        $this->manageId = $manageId;
        $this->entityId = $entityId;
        $this->nameNl = $nameNl;
        $this->nameEn = $nameEn;
    }

    /**
     * @return string
     */
    public function getManageId()
    {
        return $this->manageId;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getNameNl()
    {
        return $this->nameNl;
    }

    /**
     * @return string
     */
    public function getNameEn()
    {
        return $this->nameEn;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return (empty($this->nameNl) ? $this->nameEn : $this->nameNl);
    }
}
