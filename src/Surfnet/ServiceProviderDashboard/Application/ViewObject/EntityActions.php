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

namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

class EntityActions
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var int
     */
    private $serviceId;

    /**
     * @var string
     */
    private $status;

    /**
     * @var string
     */
    private $environment;

    /**
     * @param string $id
     * @param string $status
     * @param string $environment
     */
    public function __construct($id, $serviceId, $status, $environment)
    {
        $this->id = $id;
        $this->serviceId = $serviceId;
        $this->status = $status;
        $this->environment = $environment;
    }

    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getServiceId()
    {
        return $this->serviceId;
    }

    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return bool
     */
    public function allowEditAction()
    {
        return $this->status == 'draft';
    }

    /**
     * @return bool
     */
    public function allowCopyAction()
    {
        $isPublishedTestEntity = ($this->status == 'published' && $this->environment == 'test');
        $isPublishedProdEntity = ($this->status == 'requested' && $this->environment == 'production');
        return $isPublishedTestEntity || $isPublishedProdEntity;
    }

    public function allowCopyToProductionAction()
    {
        return $this->status == 'published' && $this->environment == 'test';
    }

    public function allowCloneAction()
    {
        return $this->status == 'published' && $this->environment == 'production';
    }

    public function allowDeleteAction()
    {
        return true;
    }

    public function isPublishedToProduction()
    {
        return $this->status == 'published' && $this->environment == 'production';
    }

    public function isPublished()
    {
        return $this->status === 'published';
    }

    public function isRequested()
    {
        return $this->status === 'requested';
    }
}
