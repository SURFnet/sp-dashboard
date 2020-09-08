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

class MetaData
{
    /**
     * Supports 1 ACS location, the first entry is used that is passed from Manage
     */
    private $acsLocation;
    private $entityId;
    private $metaDataUrl;
    private $nameIdFormat;
    private $certData;
    private $descriptionEn;
    private $descriptionNl;
    private $nameEn;
    private $nameNl;

    /**
     * @var ContactList
     */
    private $contacts;

    /**
     * @var Organization
     */
    private $organization;

    /**
     * @var Coin
     */
    private $coin;

    /**
     * @var Logo
     */
    private $logo;

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity) - Due to mapping and input validation
     * @SuppressWarnings(PHPMD.NPathComplexity) - Due to mapping and input validation
     * @param array $data
     * @return MetaData
     */
    public static function fromApiResponse(array $data)
    {
        $metaDataFields = $data['data']['metaDataFields'];

        $entityId = isset($data['data']['entityid']) ? $data['data']['entityid'] : '';
        $metaDataUrl = isset($data['data']['metadataurl']) ? $data['data']['metadataurl'] : '';
        $acsLocation = isset($metaDataFields['AssertionConsumerService:0:Location'])
            ? $metaDataFields['AssertionConsumerService:0:Location'] : '';
        $nameIdFormat = isset($metaDataFields['NameIDFormat']) ? $metaDataFields['NameIDFormat'] : '';
        $certData = isset($metaDataFields['certData']) ? $metaDataFields['certData'] : '';
        $descriptionEn = isset($metaDataFields['description:en']) ? $metaDataFields['description:en'] : '';
        $descriptionNl = isset($metaDataFields['description:nl']) ? $metaDataFields['description:nl'] : '';
        $nameEn = isset($metaDataFields['name:en']) ? $metaDataFields['name:en'] : '';
        $nameNl = isset($metaDataFields['name:nl']) ? $metaDataFields['name:nl'] : '';

        Assert::string($entityId);
        Assert::string($metaDataUrl);
        Assert::string($acsLocation);
        Assert::string($nameIdFormat);
        Assert::string($certData);
        Assert::string($descriptionEn);
        Assert::string($descriptionNl);
        Assert::string($nameEn);
        Assert::string($nameNl);

        $contactList = ContactList::fromApiResponse($metaDataFields);
        $organization = Organization::fromApiResponse($metaDataFields);
        $coin = Coin::fromApiResponse($metaDataFields);
        $logo = Logo::fromApiResponse($metaDataFields);

        return new self(
            $entityId,
            $metaDataUrl,
            $acsLocation,
            $nameIdFormat,
            $certData,
            $descriptionEn,
            $descriptionNl,
            $nameEn,
            $nameNl,
            $contactList,
            $organization,
            $coin,
            $logo
        );
    }

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ?string $entityId,
        ?string $metaDataUrl,
        ?string $acsLocation,
        ?string $nameIdFormat,
        ?string $certData,
        ?string $descriptionEn,
        ?string $descriptionNl,
        ?string $nameEn,
        ?string $nameNl,
        ?ContactList $contacts,
        ?Organization $organization,
        ?Coin $coin,
        ?Logo $logo
    ) {
        $this->entityId = $entityId;
        $this->metaDataUrl = $metaDataUrl;
        $this->acsLocation = $acsLocation;
        $this->nameIdFormat = $nameIdFormat;
        $this->certData = $certData;
        $this->descriptionEn = $descriptionEn;
        $this->descriptionNl = $descriptionNl;
        $this->nameEn = $nameEn;
        $this->nameNl = $nameNl;
        $this->contacts = $contacts;
        $this->organization = $organization;
        $this->coin = $coin;
        $this->logo = $logo;
    }

    public function getEntityId(): ?string
    {
        return $this->entityId;
    }

    public function getMetaDataUrl(): ?string
    {
        return $this->metaDataUrl;
    }

    public function getAcsLocation(): ?string
    {
        return $this->acsLocation;
    }

    public function getNameIdFormat(): ?string
    {
        return $this->nameIdFormat;
    }

    public function getCertData(): ?string
    {
        return $this->certData;
    }

    public function getDescriptionEn(): ?string
    {
        return $this->descriptionEn;
    }

    public function getDescriptionNl(): ?string
    {
        return $this->descriptionNl;
    }

    public function getNameEn(): ?string
    {
        return $this->nameEn;
    }

    public function getNameNl(): ?string
    {
        return $this->nameNl;
    }

    public function getContacts(): ?ContactList
    {
        return $this->contacts;
    }

    public function getOrganization(): ?Organization
    {
        return $this->organization;
    }

    public function getCoin(): ?Coin
    {
        return $this->coin;
    }

    public function getLogo(): ?Logo
    {
        return $this->logo;
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function merge(?MetaData $metaData)
    {
        if ($metaData === null) {
            $this->entityId = null;
            $this->metaDataUrl = null;
            $this->acsLocation = null;
            $this->nameIdFormat = null;
            $this->certData = null;
            $this->descriptionEn = null;
            $this->descriptionNl = null;
            $this->nameEn = null;
            $this->nameNl = null;
            $this->coin = null;
            $this->contacts->merge(null);
            $this->organization->merge(null);
            $this->logo->merge(null);
            return;
        }
        $this->entityId = is_null($metaData->getEntityId()) ? null : $metaData->getEntityId();
        $this->metaDataUrl = is_null($metaData->getMetaDataUrl()) ? null : $metaData->getMetaDataUrl();
        $this->acsLocation = is_null($metaData->getAcsLocation()) ? null : $metaData->getAcsLocation();
        $this->nameIdFormat = is_null($metaData->getNameIdFormat()) ? null : $metaData->getNameIdFormat();
        $this->certData = is_null($metaData->getCertData()) ? null : $metaData->getCertData();
        $this->descriptionEn = is_null($metaData->getDescriptionEn()) ? null : $metaData->getDescriptionEn();
        $this->descriptionNl = is_null($metaData->getDescriptionNl()) ? null : $metaData->getDescriptionNl();
        $this->nameEn = is_null($metaData->getNameEn()) ? null : $metaData->getNameEn();
        $this->nameNl = is_null($metaData->getNameNl()) ? null : $metaData->getNameNl();
        $this->coin->merge($metaData->getCoin());
        $this->contacts->merge($metaData->getContacts());
        $this->organization->merge($metaData->getOrganization());
        $this->logo->merge($metaData->getLogo());
    }
}
