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

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SecretInterface;

interface OidcClientInterface
{
    public static function fromApiResponse(array $data);

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

    public function getGrants(): array;

    public function isPublicClient(): bool;

    public function getAccessTokenValidity(): int;

    /**
     * @return array
     */
    public function getResourceServers();

    public function resetResourceServers(): void;

    public function updateClientSecret(SecretInterface $secret): void;

    /**
     * Merges the new Oidc data with the existing data already present on the entity.
     * The home team is used to distinguish Manage tracked resource servers from
     * outside of the team the entity is associated with.
     */
    public function merge(OidcClientInterface $client, string $homeTeam): void;

    public function asArray();
}
