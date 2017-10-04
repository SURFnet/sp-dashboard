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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Service;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Symfony\Component\Validator\Constraints as Assert;

class CreateServiceCommand implements Command
{
    /**
     * @var string
     * @Assert\NotBlank
     * @Assert\Uuid
     */
    private $id;

    /**
     * @var Supplier
     * @Assert\NotNull
     */
    private $supplier;

    /**
     * @var string
     * @Assert\NotBlank
     */
    private $ticketNumber;

    /**
     * @param string $id
     * @param Supplier $supplier
     * @param string $ticketNumber
     */
    public function __construct($id, Supplier $supplier, $ticketNumber)
    {
        $this->id = $id;
        $this->supplier = $supplier;
        $this->ticketNumber = $ticketNumber;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Supplier
     */
    public function getSupplier()
    {
        return $this->supplier;
    }

    /**
     * @return string
     */
    public function getTicketNumber()
    {
        return $this->ticketNumber;
    }
}
