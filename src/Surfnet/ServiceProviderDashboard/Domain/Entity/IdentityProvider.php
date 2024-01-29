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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

use Webmozart\Assert\Assert;

class IdentityProvider
{
    /**
     * @param string $nameNl
     */
    public function __construct(
        private readonly string $manageId,
        private readonly string $entityId,
        private ?string $nameNl,
        private string $nameEn,
    ) {
        Assert::stringNotEmpty($manageId);
        Assert::stringNotEmpty($entityId);
        Assert::nullOrString($nameNl);
        Assert::stringNotEmpty($nameEn);

        $this->nameNl = (string) $nameNl;
        $this->nameEn = (string) $nameEn;
    }

    /**
     * @return string
     */
    public function getManageId(): string
    {
        return $this->manageId;
    }

    /**
     * @return string
     */
    public function getEntityId(): string
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getNameNl(): ?string
    {
        return $this->nameNl;
    }

    /**
     * @return string
     */
    public function getNameEn(): string
    {
        return $this->nameEn;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return ($this->nameNl === null || $this->nameNl === '' || $this->nameNl === '0' ? $this->nameEn : $this->nameNl);
    }
}
