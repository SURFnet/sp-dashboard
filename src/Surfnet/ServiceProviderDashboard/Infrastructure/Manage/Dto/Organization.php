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

class Organization
{
    private $nameEn;
    private $displayNameEn;
    private $urlEn;
    private $nameNl;
    private $displayNameNl;
    private $urlNl;

    /**
     * @param string $nameEn
     * @param string $displayNameEn
     * @param string $urlEn
     * @param string $nameNl
     * @param string $displayNameNl
     * @param string $urlNl
     */
    public function __construct($nameEn, $displayNameEn, $urlEn, $nameNl, $displayNameNl, $urlNl)
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
