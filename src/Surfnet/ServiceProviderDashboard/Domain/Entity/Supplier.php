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

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField Fields of this class are not yet used, remove this once they are used)
 */
class Supplier
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
     * @ORM\Column(type="guid", unique=true)
     */
    private $guid;

    /**
     * @var string
     *
     * @ORM\Column(length=255)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(length=255)
     */
    private $teamName;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Contact", mappedBy="supplier", cascade={"persist"}, orphanRemoval=true)
     */
    private $contacts;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Service", mappedBy="supplier", cascade={"persist"}, orphanRemoval=true)
     */
    private $services;
}
