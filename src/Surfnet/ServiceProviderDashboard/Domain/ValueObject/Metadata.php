<?php

declare(strict_types=1);

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

use Surfnet\ServiceProviderDashboard\Domain\Exception\AttributeNotFoundException;

/**
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Metadata
{
    /**
     * @var array
     */
    public $acsLocations = [];

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

    public $attributes = [];

    /**
     * @var string
     */
    public $organizationNameEn;

    /**
     * @var string
     */
    public $organizationDisplayNameEn;

    /**
     * @var string
     */
    public $organizationUrlEn;

    /**
     * @var string
     */
    public $organizationNameNl;

    /**
     * @var string
     */
    public $organizationDisplayNameNl;

    /**
     * @var string
     */
    public $organizationUrlNl;

    public function setAttribute(string $property, Attribute $value): void
    {
        $this->attributes[$property] = $value;
    }

    /**
     * @throws AttributeNotFoundException
     */
    public function getAttribute(string $property): Attribute
    {
        if (array_key_exists($property, $this->attributes)) {
            return $this->attributes[$property];
        }
        throw new AttributeNotFoundException(sprintf('Invalid attribute \'%s\' requested', $property));
    }
}
