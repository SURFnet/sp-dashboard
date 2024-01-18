<?php

//declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Legacy\Metadata;

use DOMDocument;
use Psr\Log\LoggerInterface;
use SimpleXMLElement;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Metadata\CertificateParserInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeService;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;

class Parser implements ParserInterface
{
    final public const NS_LANG = 'http://www.w3.org/XML/1998/namespace';

    /**
     * The namespace for the SAML 2 metadata.
     */
    final public const NS_MD = 'urn:oasis:names:tc:SAML:2.0:metadata';

    final public const XMLDSIGNS = 'http://www.w3.org/2000/09/xmldsig#';

    final public const SAML2_XML_MDUI_UI_INFO_NS = 'urn:oasis:names:tc:SAML:metadata:ui';

    final public const BINDING_HTTP_POST = 'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST';

    final public const NAMEID_FORMAT_TRANSIENT = 'urn:oasis:names:tc:SAML:2.0:nameid-format:transient';

    public function __construct(private readonly CertificateParserInterface $certParser, private readonly AttributeService $attributeService, private readonly string $schemaLocation, private readonly LoggerInterface $logger)
    {
    }

    /**
     * @param  string $xml
     * @return Metadata
     * @throws InvalidArgumentException
     * @throws ParserException
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function parseXml($xml): \Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata
    {
        $this->validate($xml);
        $xml = simplexml_load_string($xml);
        $metadata = new Metadata();

        if ($this->describesMultipleEntities($xml)) {
            $count = $this->countEntities($xml);
            $this->logger->info(
                sprintf('The metadata describes %d entities (has EntitiesDescriptor container)', $count)
            );
            if ($count > 1) {
                throw new ParserException(
                    'Using metadata that describes multiple entities is not supported. Please provide metadata ' .
                    'describing a single SP entity.'
                );
            }
            // Metadata uses EntitiesDescriptor but describes only one entity, using that entity for import.
            $children = $xml->children(self::NS_MD)->EntityDescriptor;
            $entityDescriptorAttributes = $children->attributes();
            $metadata->entityId = (string)$entityDescriptorAttributes['entityID'];
        } else {
            $metadata->entityId = (string)$xml['entityID'];
            $children = $xml->children(self::NS_MD);
        }

        $descriptor = $children->SPSSODescriptor;
        $contactPersons = $children->ContactPerson;

        if (property_exists($children, 'Organization') && $children->Organization !== null) {
            $this->parseOrganization($children->Organization, $metadata);
        }

        $this->parseAssertionConsumerService($descriptor, $metadata);
        $this->parseNameIdFormat($descriptor, $metadata);

        if (property_exists($descriptor, 'KeyDescriptor') && $descriptor->KeyDescriptor !== null) {
            $this->parseCertificate($descriptor, $metadata);
        }

        if (property_exists($descriptor, 'Extensions') && $descriptor->Extensions !== null) {
            $this->parseUi($descriptor, $metadata);
        }

        $this->parseContactPersons($contactPersons, $metadata);

        if (property_exists($descriptor, 'AttributeConsumingService') && $descriptor->AttributeConsumingService !== null) {
            $this->parseAttributes($descriptor, $metadata);
        }

        return $metadata;
    }

    /**
     * @param SimpleXMLElement $descriptor
     */
    private function parseNameIdFormat($descriptor, Metadata $metadata): void
    {
        $metadata->nameIdFormat = self::NAMEID_FORMAT_TRANSIENT;

        if (property_exists($descriptor, 'NameIDFormat') && $descriptor->NameIDFormat !== null) {
            $metadata->nameIdFormat = (string)$descriptor->NameIDFormat;
        }
    }

    /**
     * @param SimpleXMLElement $descriptor
     *
     * @throws InvalidArgumentException
     */
    private function parseAssertionConsumerService($descriptor, Metadata $metadata): void
    {
        if (!property_exists($descriptor, 'AssertionConsumerService') || $descriptor->AssertionConsumerService === null) {
            throw new InvalidArgumentException('Invalid metadata XML');
        }

        foreach ($descriptor->AssertionConsumerService as $acs) {
            $acs = $acs->attributes();

            if ((string)$acs['Binding'] === self::BINDING_HTTP_POST) {
                $metadata->acsLocations[] = (string)$acs['Location'];
            }

            if ((int)$acs['index'] > 9) {
                throw new InvalidArgumentException(
                    'The metadata should not contain an ACS with an index larger than 9.'
                );
            }
        }
    }

    /**
     * @param SimpleXMLElement $descriptor
     */
    private function parseCertificate(object $descriptor, Metadata $metadata): void
    {
        foreach ($descriptor->KeyDescriptor->children(self::XMLDSIGNS) as $keyInfo) {
            $metadata->certificate = $this->certParser->parse((string)$keyInfo->X509Data->X509Certificate);
            break;
        }
    }

    /**
     * @param SimpleXMLElement $descriptor
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function parseUi(object $descriptor, Metadata $metadata): void
    {
        $ui = $descriptor->Extensions->children(self::SAML2_XML_MDUI_UI_INFO_NS)->UIInfo;

        $metadata->logoUrl = (string)$ui->Logo;

        if (!empty($ui->Description)) {
            foreach ($ui->Description as $description) {
                $lang = $description->attributes(static::NS_LANG);
                $lang = $lang['lang'];

                switch ($lang) {
                    case 'en':
                        $metadata->descriptionEn = (string)$description;
                        break;

                    case 'nl':
                        $metadata->descriptionNl = (string)$description;
                        break;
                }
            }
        }

        if (!empty($ui->DisplayName)) {
            foreach ($ui->DisplayName as $name) {
                $lang = $name->attributes(static::NS_LANG);
                $lang = $lang['lang'];

                switch ($lang) {
                    case 'en':
                        $metadata->nameEn = (string)$name;
                        break;

                    case 'nl':
                        $metadata->nameNl = (string)$name;
                        break;
                }
            }
        }

        if (!empty($ui->InformationURL)) {
            foreach ($ui->InformationURL as $url) {
                $lang = $url->attributes(static::NS_LANG);
                $lang = $lang['lang'];

                switch ($lang) {
                    case 'en':
                        $metadata->applicationUrlEn = (string)$url;
                        break;

                    case 'nl':
                        $metadata->applicationUrlNl = (string)$url;
                        break;
                }
            }
        }
    }

    /**
     * @param SimpleXMLElement $persons
     */
    private function parseContactPersons($persons, Metadata $metadata): void
    {
        foreach ($persons as $person) {
            $contact = new Contact();
            $contact->setFirstName((string)$person->GivenName);
            $contact->setLastName((string)$person->SurName);
            $contact->setEmail((string)$person->EmailAddress);
            $contact->setPhone((string)$person->TelephoneNumber);

            $type = $person->attributes();
            switch ($type['contactType']) {
                case 'support':
                    $metadata->supportContact = $contact;
                    break;

                case 'technical':
                    $metadata->technicalContact = $contact;
                    break;

                case 'administrative':
                    $metadata->administrativeContact = $contact;
                    break;
            }
        }
    }

    /**
     * @param SimpleXMLElement $organization
     */
    private function parseOrganization($organization, Metadata $metadata): void
    {
        foreach ($organization->OrganizationName as $element) {
            $this->setMultilingualMetadataProperty($metadata, $element, 'organizationName');
        }

        foreach ($organization->OrganizationDisplayName as $element) {
            $this->setMultilingualMetadataProperty($metadata, $element, 'organizationDisplayName');
        }

        foreach ($organization->OrganizationURL as $element) {
            $this->setMultilingualMetadataProperty($metadata, $element, 'organizationUrl');
        }
    }

    /**
     * @param SimpleXMLElement $element
     */
    private function setMultilingualMetadataProperty(Metadata $metadata, $element, string $propertyName): void
    {
        $lang = $element->attributes(static::NS_LANG);
        $lang = $lang['lang'];

        $propertyNameWithLanguage = $propertyName.ucfirst(strtolower((string) $lang));
        $metadata->{$propertyNameWithLanguage} = (string)$element;
    }

    /**
     * @param SimpleXMLElement $descriptor
     */
    private function parseAttributes(object $descriptor, Metadata $metadata): void
    {
        foreach ($descriptor->AttributeConsumingService->RequestedAttribute as $requestedAttribute) {
            $attr = new Attribute();
            $attr->setRequested(true);
            $attr->setMotivation('');

            $attributes = $requestedAttribute->attributes();

            foreach ($this->attributeService->getAttributeTypeAttributes() as $attribute) {
                if (in_array($attributes['Name'], $attribute->getUrns())) {
                    $metadata->setAttribute($attribute->getName(), $attr);
                }
            }
        }
    }

    /**
     * @param string $xml
     *
     * @throws ParserException
     */
    private function validate($xml): void
    {
        libxml_use_internal_errors(true);

        // Web tests use the dom crawler, if any xml errors are encountered by using the crawler they are stored in the
        // error buffer. Clearing the buffer before validating the schema prevents the showing of irrelevant messages to
        //the end user.
        libxml_clear_errors();

        $doc = new DOMDocument();
        $doc->loadXml($xml);

        if (!$doc->schemaValidate($this->schemaLocation.'/schemas/surf.xsd')) {
            $errors = libxml_get_errors();
            libxml_clear_errors();

            $this->logger->error('Metadata XML validation errors:', $errors);

            $ex = new ParserException('The metadata XML is invalid considering the associated XSD');
            $ex->setParserErrors($errors);
            throw $ex;
        }
    }

    /**
     * Tests if the metadata describes a single or multiple entities.
     *
     * Excerpt from https://docs.oasis-open.org/security/saml/v2.0/saml-metadata-2.0-os.pdf
     *
     * 2.3 Root Elements
     * A SAML meta data instance describes either a single entity or
     * multiple entities. In the former case, the root element MUST be
     * <EntityDescriptor>. In the latter case, the root element MUST
     * be <EntitiesDescriptor>.
     *
     * @return bool
     */
    private function describesMultipleEntities(SimpleXMLElement $xml): bool
    {
        return $xml->getName() === 'EntitiesDescriptor';
    }

    private function countEntities(SimpleXMLElement $xml)
    {
        return $xml->children(self::NS_MD)->count();
    }
}
