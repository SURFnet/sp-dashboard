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

class Organization
{
    private $nameEn;
    private $displayNameEn;
    private $urlEn;
    private $nameNl;
    private $displayNameNl;
    private $urlNl;

    public static function fromApiResponse(array $metaDataFields)
    {
        $nameEn = isset($metaDataFields['OrganizationName:en']) ? $metaDataFields['OrganizationName:en'] : '';
        $displayNameEn = isset($metaDataFields['OrganizationDisplayName:en'])
            ? $metaDataFields['OrganizationDisplayName:en'] : '';
        $urlEn = isset($metaDataFields['OrganizationURL:en']) ? $metaDataFields['OrganizationURL:en'] : '';
        $nameNl = isset($metaDataFields['OrganizationName:nl']) ? $metaDataFields['OrganizationName:nl'] : '';
        $displayNameNl = isset($metaDataFields['OrganizationDisplayName:nl'])
            ? $metaDataFields['OrganizationDisplayName:nl'] : '';
        $urlNl = isset($metaDataFields['OrganizationURL:nl']) ? $metaDataFields['OrganizationURL:nl'] : '';

        Assert::string($nameEn);
        Assert::string($displayNameEn);
        Assert::string($urlEn);
        Assert::string($nameNl);
        Assert::string($displayNameNl);
        Assert::string($urlNl);

        return new self($nameEn, $displayNameEn, $urlEn, $nameNl, $displayNameNl, $urlNl);
    }

    /**
     * @param string $nameEn
     * @param string $displayNameEn
     * @param string $urlEn
     * @param string $nameNl
     * @param string $displayNameNl
     * @param string $urlNl
     */
    private function __construct($nameEn, $displayNameEn, $urlEn, $nameNl, $displayNameNl, $urlNl)
    {
        $this->nameEn = $nameEn;
        $this->displayNameEn = $displayNameEn;
        $this->urlEn = $urlEn;
        $this->nameNl = $nameNl;
        $this->displayNameNl = $displayNameNl;
        $this->urlNl = $urlNl;
    }

    public function getNameEn()
    {
        return $this->nameEn;
    }

    public function getDisplayNameEn()
    {
        return $this->displayNameEn;
    }

    public function getUrlEn()
    {
        return $this->urlEn;
    }

    public function getNameNl()
    {
        return $this->nameNl;
    }

    public function getDisplayNameNl()
    {
        return $this->displayNameNl;
    }

    public function getUrlNl()
    {
        return $this->urlNl;
    }
}
