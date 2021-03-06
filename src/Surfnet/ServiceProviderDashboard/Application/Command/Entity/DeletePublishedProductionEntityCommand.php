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
use Symfony\Component\Validator\Constraints as Assert;

class DeletePublishedProductionEntityCommand implements Command
{
    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Uuid
     */
    private $manageId;

    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Uuid
     */
    private $protocol;

    /**
     * @param string $manageId
     * @param $protocol
     */
    public function __construct($manageId, $protocol)
    {
        $this->manageId = $manageId;
        $this->protocol = $protocol;
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
    public function getProtocol()
    {
        return $this->protocol;
    }
}
