<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

interface OidcClientInterface
{
    /**
     * @param array $data
     * @param string $manageProtocol
     * @return OidcClient|null
     */
    public static function fromApiResponse(array $data, $manageProtocol);

    /**
     * @return string
     */
    public function getClientId();

    /**
     * @return string
     */
    public function getClientSecret();

    /**
     * @return array
     */
    public function getRedirectUris();

    /**
     * @return string
     */
    public function getGrantType();

    /**
     * @return array
     */
    public function getScope();

    /**
     * @return bool
     */
    public function isPublicClient();

    public function getAccessTokenValidity(): int;

    /**
     * @return array
     */
    public function getResourceServers();

    public function isPlaygroundEnabled(): bool;
}
