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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @ORM\Entity(repositoryClass="Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\EntityRemovalRequestRepository")
 */
class EntityRemovalRequest
{
    /**
     * @var string
     *
     * @ORM\Id
     * @ORM\Column(type="string", length=255)
     */
    private $ticketKey;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    private $manageId;

    /**
     * @param string $ticketKey
     * @param string $manageId
     */
    public function __construct($ticketKey, $manageId)
    {
        $this->ticketKey = $ticketKey;
        $this->manageId = $manageId;
    }

    /**
     * @return string
     */
    public function getTicketKey()
    {
        return $this->ticketKey;
    }

    /**
     * @return string
     */
    public function getManageId()
    {
        return $this->manageId;
    }
}
