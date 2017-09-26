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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity;

use Doctrine\ORM\Mapping as ORM;
use Surfnet\ServiceProviderDashboard\Domain\Model\Contact as ContactPerson;
use Surfnet\ServiceProviderDashboard\Domain\Model\Attribute;
use Gedmo\Mapping\Annotation as Gedmo;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @package Surfnet\ServiceProviderDashboard\Entity
 *
 * @ORM\Entity
 *
 * @SuppressWarnings(PHPMD.UnusedPrivateField Fields of this class are not yet used, remove this once they are used)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class Service
{
    const STATE_DRAFT = 0;
    const STATE_PUBLISHED = 1;
    const STATE_FINISHED = 2;
    const ENVIRONMENT_CONNECT = 'connect';
    const ENVIRONMENT_PRODUCTION = 'production';

    /**
     * @var string
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var bool
     * @ORM\Column(type="boolean")
     */
    private $archived = false;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $environment = self::ENVIRONMENT_CONNECT;

    /**
     * @var int
     * @ORM\Column(type="integer")
     */
    private $status;

    /**
     * @var \DateTime $created
     *
     * @Gedmo\Timestampable(on="create")
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var \DateTime $updated
     *
     * @Gedmo\Timestampable(on="update")
     * @ORM\Column(type="datetime")
     */
    private $updated;

    /**
     * @var string
     * @ORM\Column(type="string")
     */
    private $ticketNo;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $janusId;

    /**
     * Metadata URL that import last happened from.
     *
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $importUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $metadataUrl;

    /**
     * SAML XML Metadata for entity.
     *
     * Imported from metadataurl.
     *
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $metadataXml;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $acsLocation;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $entityId;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $certificate;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $logoUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $nameNl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $nameEn;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionNl;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $descriptionEn;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $applicationUrl;

    /**
     * @var string
     * @ORM\Column(type="string", nullable=true)
     */
    private $eulaUrl;

    /**
     * @var ContactPerson
     * @ORM\Column(type="object", nullable=true)
     */
    private $administrativeContact;

    /**
     * @var ContactPerson
     * @ORM\Column(type="object", nullable=true)
     */
    private $technicalContact;

    /**
     * @var ContactPerson
     * @ORM\Column(type="object", nullable=true)
     */
    private $supportContact;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $givenNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $surNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $commonNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $displayNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $emailAddressAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $organizationAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $organizationTypeAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $affiliationAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     * @Assert\Type(type="AppBundle\Model\Attribute")
     * @Assert\Valid()
     */
    private $entitlementAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $principleNameAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $uidAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $preferredLanguageAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $personalCodeAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $scopedAffiliationAttribute;

    /**
     * @var Attribute
     * @ORM\Column(type="object", nullable=true)
     */
    private $eduPersonTargetedIDAttribute;

    /**
     * @var string
     * @ORM\Column(type="text", nullable=true)
     */
    private $comments;

    /**
     * @var Supplier
     *
     * @ORM\ManyToOne(targetEntity="Supplier", inversedBy="services")
     * @ORM\JoinColumn(nullable=false)
     */
    private $supplier;
}
