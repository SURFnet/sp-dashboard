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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Webmozart\Assert\Assert;

class Coin
{
    private $signatureMethod;
    private $serviceTeamId;
    private $originalMetadataUrl;
    private $excludeFromPush;
    private $applicationUrl;
    private $eula;
    private $oidcClient;

    public static function fromApiResponse(array $metaDataFields)
    {
        $signatureMethod = isset($metaDataFields['coin:signature_method'])
            ? $metaDataFields['coin:signature_method'] : '';
        $serviceTeamId = isset($metaDataFields['coin:service_team_id'])
            ? $metaDataFields['coin:service_team_id'] : '';
        $originalMetadataUrl = isset($metaDataFields['coin:original_metadata_url'])
            ? $metaDataFields['coin:original_metadata_url'] : '';
        $applicationUrl = isset($metaDataFields['coin:application_url'])
            ? $metaDataFields['coin:application_url'] : '';
        $eula = isset($metaDataFields['coin:eula'])
            ? $metaDataFields['coin:eula'] : '';
        $excludeFromPush = isset($metaDataFields['coin:exclude_from_push'])
            ? (int) $metaDataFields['coin:exclude_from_push'] : null;
        $oidcClient = isset($metaDataFields['coin:oidc_client'])
            ? (int) $metaDataFields['coin:oidc_client'] : 0;

        Assert::string($signatureMethod);
        Assert::string($serviceTeamId);
        Assert::string($originalMetadataUrl);
        Assert::string($applicationUrl);
        Assert::string($eula);
        Assert::nullOrIntegerish($excludeFromPush);
        Assert::integer($oidcClient);

        return new self(
            $signatureMethod, $serviceTeamId, $originalMetadataUrl, $excludeFromPush, $applicationUrl, $eula, $oidcClient
        );
    }

    public function __construct(
        ?string $signatureMethod,
        ?string $serviceTeamId,
        ?string $originalMetadataUrl,
        ?string $excludeFromPush,
        ?string $applicationUrl,
        ?string $eula,
        ?int $oidcClient
    ) {
        $this->signatureMethod = $signatureMethod;
        $this->serviceTeamId = $serviceTeamId;
        $this->originalMetadataUrl = $originalMetadataUrl;
        $this->excludeFromPush = $excludeFromPush;
        $this->applicationUrl = $applicationUrl;
        $this->eula = $eula;
        $this->oidcClient = $oidcClient;
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

    public function getApplicationUrl()
    {
        return $this->applicationUrl;
    }

    public function getEula()
    {
        return $this->eula;
    }

    public function getOidcClient()
    {
        return $this->oidcClient;
    }

    public function merge(?Coin $coin)
    {
        // When the new Coin data is not set, reset the data to reflect that state (not set)
        if (is_null($coin)) {
            $this->signatureMethod = null;
            $this->serviceTeamId = null;
            $this->originalMetadataUrl = null;
            $this->excludeFromPush = null;
            $this->applicationUrl = null;
            $this->eula = null;
            $this->oidcClient = null;
            return;
        }

        // Overwrite the current data with that from the new Coin data
        $this->signatureMethod = is_null($coin->getSignatureMethod()) ? null : $coin->getSignatureMethod();
        $this->serviceTeamId = is_null($coin->getServiceTeamId()) ? null : $coin->getServiceTeamId();
        $this->originalMetadataUrl = is_null($coin->getOriginalMetadataUrl()) ? null : $coin->getOriginalMetadataUrl();
        $this->excludeFromPush = is_null($coin->getExcludeFromPush()) ? null : $coin->getExcludeFromPush();
        $this->applicationUrl = is_null($coin->getApplicationUrl()) ? null : $coin->getApplicationUrl();
        $this->eula = is_null($coin->getEula()) ? null : $coin->getEula();
        $this->oidcClient = is_null($coin->getOidcClient()) ? null : $coin->getOidcClient();
    }
}
