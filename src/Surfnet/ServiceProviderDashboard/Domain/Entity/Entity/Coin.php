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
use Surfnet\ServiceProviderDashboard\Domain\Exception\TypeOfServiceException;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\TypeOfServiceCollection;
use Webmozart\Assert\Assert;

/**
 * @SuppressWarnings(PHPMD.ExcessiveParameterList)
 */
class Coin implements Comparable
{
    public static function fromApiResponse(array $metaDataFields): self
    {
        $signatureMethod = $metaDataFields['coin:signature_method'] ?? '';
        $serviceTeamId = $metaDataFields['coin:service_team_id'] ?? '';
        $originalMetadataUrl = $metaDataFields['coin:original_metadata_url'] ?? '';
        $applicationUrl = $metaDataFields['coin:application_url'] ?? '';
        $contractualBase = $metaDataFields['coin:contractual_base'] ?? null;
        $eula = $metaDataFields['coin:eula'] ?? '';
        $excludeFromPush = isset($metaDataFields['coin:exclude_from_push'])
            ? (int)$metaDataFields['coin:exclude_from_push'] : null;
        $oidcClient = isset($metaDataFields['coin:oidc_client'])
            ? (int)$metaDataFields['coin:oidc_client'] : 0;
        $idpVisibleOnly = $metaDataFields['coin:ss:idp_visible_only'] ?? null;

        $typeOfService = TypeOfServiceCollection::createFromManageResponse($metaDataFields);
        Assert::string($signatureMethod);
        Assert::string($serviceTeamId);
        Assert::string($originalMetadataUrl);
        Assert::string($applicationUrl);
        Assert::string($eula);
        Assert::nullOrIntegerish($excludeFromPush);
        Assert::integer($oidcClient);
        Assert::nullOrString($contractualBase);
        Assert::nullOrBoolean($idpVisibleOnly);

        return new self(
            $signatureMethod,
            $serviceTeamId,
            $originalMetadataUrl,
            $excludeFromPush,
            $applicationUrl,
            $typeOfService,
            $eula,
            $oidcClient,
            $contractualBase,
            $idpVisibleOnly,
        );
    }

    public function __construct(
        private ?string $signatureMethod,
        private ?string $serviceTeamId,
        private ?string $originalMetadataUrl,
        private null|string|int $excludeFromPush,
        private ?string $applicationUrl,
        private ?TypeOfServiceCollection $typeOfService,
        private ?string $eula,
        private ?int $oidcClient,
        private ?string $contractualBase,
        private ?bool $idpVisibleOnly,
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

    public function getTypeOfService(): TypeOfServiceCollection
    {
        if ($this->typeOfService === null) {
            throw new TypeOfServiceException('Type of service is not set on the Coin');
        }
        return $this->typeOfService;
    }

    public function hasTypeOfService(): bool
    {
        return $this->typeOfService !== null;
    }

    public function getContractualBase(): ?string
    {
        return $this->contractualBase;
    }

    public function setContractualBase(string $contractualBase): void
    {
        $this->contractualBase = $contractualBase;
    }


    public function isIdpVisibleOnly(): ?bool
    {
        return $this->idpVisibleOnly;
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
        $this->typeOfService = $coin->getTypeOfService();
        $this->contractualBase = $coin->getContractualBase();
        $this->idpVisibleOnly = $coin->isIdpVisibleOnly();
    }

    public function asArray(): array
    {
        return [
            'metaDataFields.coin:application_url' => $this->getApplicationUrl(),
            'metaDataFields.coin:eula' => $this->getEula(),
            'metaDataFields.coin:exclude_from_push' => $this->getExcludeFromPush(),
            'metaDataFields.coin:oidc_client' => $this->getOidcClient(),
            'metaDataFields.coin:original_metadata_url' => $this->getOriginalMetadataUrl(),
            'metaDataFields.coin:ss:type_of_service:en' => $this->getTypeOfService()->getServicesAsEnglishString(),
            'metaDataFields.coin:ss:type_of_service:nl' => $this->getTypeOfService()->getServicesAsDutchString(),
            'metaDataFields.coin:contractual_base' => $this->getContractualBase(),
            'metaDataFields.coin:ss:idp_visible_only' => $this->isIdpVisibleOnly(),
        ];
    }

    public function setTypeOfService(TypeOfServiceCollection $typeOfService): void
    {
        $this->typeOfService = $typeOfService;
    }
}
