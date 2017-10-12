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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client;

use GuzzleHttp\ClientInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\InvalidJsonException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\AccessDeniedException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\MalformedResponseException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Http\Exception\UnreadableResourceException;

class PublishRequest
{

    public $metadataXml;

    /**
     * @param Service $service
     *
     * @return PublishRequest
     */
    public static function from(Service $service)
    {
        $request = new self;

        $request->metadataXml = $service->getMetadataXml();

        return $request;
    }
}
