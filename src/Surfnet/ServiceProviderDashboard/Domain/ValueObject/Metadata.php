<?php

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\ValueObject;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Metadata
{
    /**
     * @var string
     */
    public $acsLocation;

    /**
     * @var string
     */
    public $entityId;

    /**
     * @var string
     */
    public $certificate;

    /**
     * @var string
     */
    public $nameIdFormat;

    /**
     * @var string
     */
    public $logoUrl;

    /**
     * @var string
     */
    public $nameEn;

    /**
     * @var string
     */
    public $nameNl;

    /**
     * @var string
     */
    public $descriptionEn;

    /**
     * @var string
     */
    public $descriptionNl;

    /**
     * @var string
     */
    public $applicationUrlEn;

    /**
     * @var string
     */
    public $applicationUrlNl;

    /**
     * @var Contact
     */
    public $administrativeContact;

    /**
     * @var Contact
     */
    public $supportContact;

    /**
     * @var Contact
     */
    public $technicalContact;

    /**
     * @var Attribute
     */
    public $givenNameAttribute;

    /**
     * @var Attribute
     */
    public $surNameAttribute;

    /**
     * @var Attribute
     */
    public $commonNameAttribute;

    /**
     * @var Attribute
     */
    public $displayNameAttribute;

    /**
     * @var Attribute
     */
    public $emailAddressAttribute;

    /**
     * @var Attribute
     */
    public $organizationAttribute;

    /**
     * @var Attribute
     */
    public $organizationTypeAttribute;

    /**
     * @var Attribute
     */
    public $affiliationAttribute;

    /**
     * @var Attribute
     */
    public $entitlementAttribute;

    /**
     * @var Attribute
     */
    public $principleNameAttribute;

    /**
     * @var Attribute
     */
    public $uidAttribute;

    /**
     * @var Attribute
     */
    public $preferredLanguageAttribute;

    /**
     * @var Attribute
     */
    public $personalCodeAttribute;

    /**
     * @var Attribute
     */
    public $scopedAffiliationAttribute;

    /**
     * @var Attribute
     */
    public $eduPersonTargetedIDAttribute;
}
