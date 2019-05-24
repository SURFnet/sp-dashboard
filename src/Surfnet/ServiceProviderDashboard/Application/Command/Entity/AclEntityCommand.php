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
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Symfony\Component\Validator\Constraints as Assert;

/**
 *
 */
class AclEntityCommand implements Command
{
    /** @var IdentityProvider[] */
    private $providers;

    /**
     * @var IdentityProvider[]
     * @Assert\All({
     *     @Assert\NotBlank(),
     *     @Assert\Type(type="Surfnet\ServiceProviderDashboard\Domain\ValueObject\IdentityProvider")
     * })
     */
    private $selected;

    public function __construct(array $availableProviders)
    {
        $this->providers = $availableProviders;
    }

    /**
     * @return IdentityProvider[]
     */
    public function getAvailable()
    {
        return $this->providers;
    }

    /**
     * @return IdentityProvider[]
     */
    public function getSelected()
    {
        return $this->selected;
    }

    /**
     * @param array $idps
     */
    public function setSelected(array $idps)
    {
        $this->selected = $idps;
    }
}
