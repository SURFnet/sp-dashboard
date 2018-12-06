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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto;

class Coin
{
    private $signatureMethod;
    private $serviceTeamId;
    private $originalMetadataUrl;
    private $excludeFromPush;

    /**
     * @param string $signatureMethod
     * @param string $serviceTeamId
     * @param string $originalMetadataUrl
     * @param string $excludeFromPush
     */
    public function __construct($signatureMethod, $serviceTeamId, $originalMetadataUrl, $excludeFromPush)
    {
        $this->signatureMethod = $signatureMethod;
        $this->serviceTeamId = $serviceTeamId;
        $this->originalMetadataUrl = $originalMetadataUrl;
        $this->excludeFromPush = $excludeFromPush;
    }

    public function getSignatureMethod()
    {
        return $this->signatureMethod;
    }

    public function getServiceTeamId()
    {
        return $this->serviceTeamId;
    }

    public function getOriginalMetadataUrl()
    {
        return $this->originalMetadataUrl;
    }

    public function getExcludeFromPush()
    {
        return $this->excludeFromPush;
    }
}
