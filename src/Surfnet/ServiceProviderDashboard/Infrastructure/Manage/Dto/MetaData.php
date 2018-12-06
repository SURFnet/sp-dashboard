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

class MetaData
{
    /**
     * Supports 1 ACS location, the first entry is used that is passed from Manage
     */
    private $acsBinding;
    private $acsLocation;

    private $nameIdFormat;
    private $signatureMethod;
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
     * @param string $acsBinding
     * @param string $acsLocation
     * @param string $nameIdFormat
     * @param string $signatureMethod
     * @param string $certData
     * @param string $descriptionEn
     * @param string $descriptionNl
     * @param string $nameEn
     * @param string $nameNl
     * @param ContactList $contacts
     * @param Organization $organization
     * @param Coin $coin
     * @param Logo $logo
     */
    public function __construct(
        $acsBinding,
        $acsLocation,
        $nameIdFormat,
        $signatureMethod,
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
        $this->acsBinding = $acsBinding;
        $this->acsLocation = $acsLocation;
        $this->nameIdFormat = $nameIdFormat;
        $this->signatureMethod = $signatureMethod;
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

    public function getAcsBinding()
    {
        return $this->acsBinding;
    }

    public function getAcsLocation()
    {
        return $this->acsLocation;
    }

    public function getNameIdFormat()
    {
        return $this->nameIdFormat;
    }

    public function getSignatureMethod()
    {
        return $this->signatureMethod;
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
