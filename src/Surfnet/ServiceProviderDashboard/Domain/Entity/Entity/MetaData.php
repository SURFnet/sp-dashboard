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

        $entityId = $data['data']['entityid'];
        $metaDataUrl = isset($data['data']['metadataurl']) ? $data['data']['metadataurl'] : '';
        $acsLocation = isset($metaDataFields['AssertionConsumerService:0:Location'])
            ? $metaDataFields['AssertionConsumerService:0:Location'] : '';
        $nameIdFormat = isset($metaDataFields['NameIDFormat']) ? $metaDataFields['NameIDFormat'] : '';
        $certData = isset($metaDataFields['certData']) ? $metaDataFields['certData'] : '';
        $descriptionEn = isset($metaDataFields['description:en']) ? $metaDataFields['description:en'] : '';
        $descriptionNl = isset($metaDataFields['description:nl']) ? $metaDataFields['description:nl'] : '';
        $nameEn = isset($metaDataFields['name:en']) ? $metaDataFields['name:en'] : '';
        $nameNl = isset($metaDataFields['name:nl']) ? $metaDataFields['name:nl'] : '';

        Assert::stringNotEmpty($entityId);
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
     * @param string $entityId,
     * @param string $metaDataUrl
     * @param string $acsLocation
     * @param string $nameIdFormat
     * @param string $certData
     * @param string $descriptionEn
     * @param string $descriptionNl
     * @param string $nameEn
     * @param string $nameNl
     * @param ContactList $contacts
     * @param Organization $organization
     * @param Coin $coin
     * @param Logo $logo
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    private function __construct(
        $entityId,
        $metaDataUrl,
        $acsLocation,
        $nameIdFormat,
        $certData,
        $descriptionEn,
        $descriptionNl,
        $nameEn,
        $nameNl,
        ContactList $contacts,
        Organization $organization,
        Coin $coin,
        Logo $logo
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

    public function getEntityId()
    {
        return $this->entityId;
    }

    public function getMetaDataUrl()
    {
        return $this->metaDataUrl;
    }

    public function getAcsLocation()
    {
        return $this->acsLocation;
    }

    public function getNameIdFormat()
    {
        return $this->nameIdFormat;
    }

    public function getCertData()
    {
        return $this->certData;
    }

    public function getDescriptionEn()
    {
        return $this->descriptionEn;
    }

    public function getDescriptionNl()
    {
        return $this->descriptionNl;
    }

    public function getNameEn()
    {
        return $this->nameEn;
    }

    public function getNameNl()
    {
        return $this->nameNl;
    }

    public function getContacts()
    {
        return $this->contacts;
    }

    public function getOrganization()
    {
        return $this->organization;
    }

    public function getCoin()
    {
        return $this->coin;
    }

    public function getLogo()
    {
        return $this->logo;
    }
}
