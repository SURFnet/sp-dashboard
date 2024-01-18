<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Comparable;
use Webmozart\Assert\Assert;
use function is_null;

class Coin implements Comparable
{
    public static function fromApiResponse(array $metaDataFields): self
    {
        $signatureMethod = $metaDataFields['coin:signature_method'] ?? '';
        $serviceTeamId = $metaDataFields['coin:service_team_id'] ?? '';
        $originalMetadataUrl = $metaDataFields['coin:original_metadata_url'] ?? '';
        $applicationUrl = $metaDataFields['coin:application_url'] ?? '';
        $eula = $metaDataFields['coin:eula'] ?? '';
        $excludeFromPush = isset($metaDataFields['coin:exclude_from_push'])
            ? (int)$metaDataFields['coin:exclude_from_push'] : null;
        $oidcClient = isset($metaDataFields['coin:oidc_client'])
            ? (int)$metaDataFields['coin:oidc_client'] : 0;

        Assert::string($signatureMethod);
        Assert::string($serviceTeamId);
        Assert::string($originalMetadataUrl);
        Assert::string($applicationUrl);
        Assert::string($eula);
        Assert::nullOrIntegerish($excludeFromPush);
        Assert::integer($oidcClient);

        return new self(
            $signatureMethod,
            $serviceTeamId,
            $originalMetadataUrl,
            $excludeFromPush,
            $applicationUrl,
            $eula,
            $oidcClient
        );
    }

    public function __construct(
        private ?string $signatureMethod,
        private ?string $serviceTeamId,
        private ?string $originalMetadataUrl,
        private null|string|int $excludeFromPush,
        private ?string $applicationUrl,
        private ?string $eula,
        private ?int $oidcClient
    ) {
    }

    public function getSignatureMethod(): ?string
    {
        return $this->signatureMethod;
    }

    public function getServiceTeamId(): ?string
    {
        return $this->serviceTeamId;
    }

    public function getOriginalMetadataUrl(): ?string
    {
        return $this->originalMetadataUrl;
    }

    public function getExcludeFromPush(): null|string|int
    {
        return $this->excludeFromPush;
    }

    public function getApplicationUrl(): ?string
    {
        return $this->applicationUrl;
    }

    public function getEula(): ?string
    {
        return $this->eula;
    }

    public function getOidcClient(): ?int
    {
        return $this->oidcClient;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function merge(Coin $coin): void
    {
        // Overwrite the current data with that from the new Coin data
        $this->signatureMethod = is_null($coin->getSignatureMethod()) ? null : $coin->getSignatureMethod();
        $this->serviceTeamId = is_null($coin->getServiceTeamId()) ? null : $coin->getServiceTeamId();
        $this->originalMetadataUrl = is_null($coin->getOriginalMetadataUrl()) ? null : $coin->getOriginalMetadataUrl();
        $this->excludeFromPush = is_null($coin->getExcludeFromPush()) ?
            $this->excludeFromPush : $coin->getExcludeFromPush();
        $this->applicationUrl = is_null($coin->getApplicationUrl()) ? null : $coin->getApplicationUrl();
        $this->eula = is_null($coin->getEula()) ? null : $coin->getEula();
        $this->oidcClient = is_null($coin->getOidcClient()) ? null : $coin->getOidcClient();
    }

    public function asArray(): array
    {
        return [
            'metaDataFields.coin:application_url' => $this->getApplicationUrl(),
            'metaDataFields.coin:eula' => $this->getEula(),
            'metaDataFields.coin:exclude_from_push' => $this->getExcludeFromPush(),
            'metaDataFields.coin:oidc_client' => $this->getOidcClient(),
            'metaDataFields.coin:original_metadata_url' => $this->getOriginalMetadataUrl(),
        ];
    }
}
