<?php

/**
 * Copyright 2017 SURFnet bv
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

namespace Surfnet\ServiceProviderDashboard\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @ORM\Entity
 */
class Contact
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(length=150)
     */
    private $nameId;

    /**
     * @var string
     *
     * @ORM\Column(length=255)
     */
    private $displayName;

    /**
     * @var string
     *
     * @ORM\Column(length=255)
     */
    private $emailAddress;

    /**
     * @var \Surfnet\ServiceProviderDashboard\Entity\Supplier
     *
     * @ORM\ManyToOne(targetEntity="Supplier", inversedBy="contacts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;
}
