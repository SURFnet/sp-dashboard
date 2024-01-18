<?php

declare(strict_types = 1);

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Dto;

class AttributeTypeInformation
{
    /**
     * @var string
     */
    public $saml20Label;

    /**
     * @var string
     */
    public $saml20Info;

    /**
     * @var string
     */
    public $oidcngLabel;

    /**
     * @var string
     */
    public $oidcngInfo;

    /**
     * @var string
     */
    public $language;

    private function __construct(
        string $saml20Label,
        string $saml20Info,
        string $oidcngLabel,
        string $oidcngInfo,
        string $language
    ) {
        $this->saml20Label = $saml20Label;
        $this->saml20Info = $saml20Info;
        $this->oidcngLabel = $oidcngLabel;
        $this->oidcngInfo = $oidcngInfo;
        $this->language = $language;
    }

    public static function fromLanguage(array $information, $language): ?AttributeTypeInformation
    {
        return new self(
            $information['saml20Label'],
            $information['saml20Info'],
            $information['oidcngLabel'],
            $information['oidcngInfo'],
            $language
        );
    }
}
