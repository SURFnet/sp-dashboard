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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @ORM\Entity(
 *     repositoryClass="Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\ServiceRepository"
 * )
 */
class Service
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
     * @ORM\Column(type="guid")
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
     * @ORM\ManyToMany(targetEntity="Contact", mappedBy="services", cascade={"persist"}, orphanRemoval=true)
     */
    private $contacts;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Entity", mappedBy="service", cascade={"persist"}, orphanRemoval=true)
     */
    private $entities;

    public function __construct()
    {
        $this->contacts = new ArrayCollection();
        $this->entities = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getGuid()
    {
        return $this->guid;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getTeamName()
    {
        return $this->teamName;
    }

    /**
     * @param string $guid
     */
    public function setGuid($guid)
    {
        $this->guid = $guid;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param string $teamName
     */
    public function setTeamName($teamName)
    {
        $this->teamName = $teamName;
    }

    /**
     * @return ArrayCollection<Entity>
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param Entity $entity
     *
     * @return Service
     */
    public function addEntity(Entity $entity)
    {
        $this->entities[] = $entity;

        $entity->setService($this);

        return $this;
    }
}
