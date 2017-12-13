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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Legacy\Metadata;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\CertificateParser;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Generator;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Parser;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

/**
 * This unit test uses the Parser ascertain the output of the Generator.
 *
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GeneratorTest extends MockeryTestCase
{
    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var Parser
     */
    private $parser;

    public function setup()
    {
        $attrRepo = new AttributesMetadataRepository(__DIR__ . '/../fixture');
        $this->generator = new Generator($attrRepo);

        $this->logger = m::mock(LoggerInterface::class);

        $schemaDir = __DIR__ . '/../../../../app/Resources/';
        $this->parser = new Parser(
            new CertificateParser(),
            $attrRepo,
            $schemaDir,
            $this->logger
        );
    }

    /**
     * @param string $metadataXml
     *
     * @return Entity
     */
    private function buildService($metadataXml)
    {
        $service = new Entity();
        $service->setNameNl('UNAMENL');
        $service->setNameEn('UNAMEEN');
        $service->setDescriptionNl('UPDATEDDESCRNL');
        $service->setDescriptionEn('UPDATEDDESCREN');
        $service->setApplicationUrl('http://www.google.nl');
        $service->setLogoUrl('http://www.google.com');
        $service->setMetadataXml($metadataXml);
        return $service;
    }

    public function test_it_can_generate()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_lean.xml'));

        $contact = new Contact();
        $contact->setFirstName('Henk');
        $contact->setLastName('Henksma');
        $contact->setEmail('henk@henkie.org');
        $contact->setPhone('+51632132145');
        $service->setSupportContact($contact);

        $contact = new Contact();
        $contact->setFirstName('Henk2');
        $contact->setLastName('Henksma2');
        $contact->setEmail('henk2@henkie.org');
        $contact->setPhone('+51639992145');
        $service->setAdministrativeContact($contact);

        $attr = new Attribute();
        $attr->setRequested(true);

        $service->setGivenNameAttribute($attr);
        $service->setUidAttribute($attr);
        $service->setEntitlementAttribute($attr);

        $attr = new Attribute();
        $attr->setRequested(false);

        $service->setCommonNameAttribute($attr);

        $xml = $this->generator->generate($service);

        $this->assertContains('<mdui:DisplayName xml:lang="nl">UNAMENL</mdui:DisplayName>', $xml);
        $this->assertContains('<mdui:DisplayName xml:lang="en">UNAMEEN</mdui:DisplayName>', $xml);
        $this->assertContains('<mdui:Description xml:lang="nl">UPDATEDDESCRNL</mdui:Description>', $xml);
        $this->assertContains('<mdui:Description xml:lang="en">UPDATEDDESCREN</mdui:Description>', $xml);
        $this->assertContains('<mdui:InformationURL xml:lang="en">http://www.google.nl</mdui:InformationURL>', $xml);
        $this->assertContains('<mdui:InformationURL xml:lang="nl">URLNL</mdui:InformationURL>', $xml);
        $this->assertContains('<mdui:Logo>http://www.google.com</mdui:Logo>', $xml);

        // Created
        $this->assertContains('<md:ContactPerson contactType="support">', $xml);
        $this->assertContains('<md:GivenName>Henk</md:GivenName>', $xml);
        $this->assertContains('<md:SurName>Henksma</md:SurName>', $xml);
        $this->assertContains('<md:EmailAddress>henk@henkie.org</md:EmailAddress>', $xml);
        $this->assertContains('<md:TelephoneNumber>+51632132145</md:TelephoneNumber>', $xml);

        // Replaced
        $this->assertContains('<md:ContactPerson contactType="administrative">', $xml);
        $this->assertContains('<md:GivenName>Henk2</md:GivenName>', $xml);
        $this->assertContains('<md:SurName>Henksma2</md:SurName>', $xml);
        $this->assertContains('<md:EmailAddress>henk2@henkie.org</md:EmailAddress>', $xml);
        $this->assertContains('<md:TelephoneNumber>+51639992145</md:TelephoneNumber>', $xml);

        // Untouched
        $this->assertContains('<md:ContactPerson contactType="technical">', $xml);
        $this->assertContains('<md:GivenName>Test</md:GivenName>', $xml);
        $this->assertContains('<md:SurName>Tester</md:SurName>', $xml);
        $this->assertContains('<md:EmailAddress>test@domain.org</md:EmailAddress>', $xml);
        $this->assertContains('<md:TelephoneNumber>123456789</md:TelephoneNumber>', $xml);
        $this->assertContains('<md:ServiceName xml:lang="en">UNAMEEN</md:ServiceName>', $xml);

        // Created non existing attribute based on first key (including FriendlyName)

        // @codingStandardsIgnoreStart
        $this->assertContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:eduPersonEntitlement" FriendlyName="Entitlement"', $xml);
        // @codingStandardsIgnoreEnd

        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.7"', $xml);

        // Replaced existing attributes based on first key (also replaced value of FriendlyName)

        // @codingStandardsIgnoreStart
        $this->assertContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:givenName" FriendlyName="Given name"', $xml);
        // @codingStandardsIgnoreEnd
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:2.5.4.42"', $xml);

        // Replaced existing attributes based on second key (also added FriendlyName)

        // @codingStandardsIgnoreStart
        $this->assertContains('md:RequestedAttribute Name="urn:oid:0.9.2342.19200300.100.1.1" isRequired="true" FriendlyName="uid"', $xml);
        // @codingStandardsIgnoreEnd
        $this->assertNotContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:uid"', $xml);

        // Non used attribute should not appear
        $this->assertNotContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:eduPersonOrgUnitDN"', $xml);
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:1.3.6.1.4.1.5923.1.1.1.4"', $xml);

        // Non requested attribute should not appear
        $this->assertNotContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:cn"', $xml);
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:2.5.4.3"', $xml);

        // Removed existing attribute based on first key
        $this->assertNotContains(
            'md:RequestedAttribute Name="urn:schac:attribute-def:schacPersonalUniqueCode"',
            $xml
        );
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:1.3.6.1.4.1.1466.155.121.1.15"', $xml);

        // Removed existing attribute based on second key
        $this->assertNotContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:preferredLanguage"', $xml);
        $this->assertNotContains('md:RequestedAttribute Name="urn:oid:2.16.840.1.113730.3.1.39"', $xml);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_it_works_with_minimal_dataset()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_leanest.xml'));

        $xml = $this->generator->generate($service);

        $this->assertContains('<mdui:DisplayName xml:lang="nl">UNAMENL</mdui:DisplayName>', $xml);
        $this->assertContains('<mdui:DisplayName xml:lang="en">UNAMEEN</mdui:DisplayName>', $xml);
        $this->assertContains('<mdui:Description xml:lang="nl">UPDATEDDESCRNL</mdui:Description>', $xml);
        $this->assertContains('<mdui:Description xml:lang="en">UPDATEDDESCREN</mdui:Description>', $xml);
        $this->assertContains('<mdui:InformationURL xml:lang="en">http://www.google.nl</mdui:InformationURL>', $xml);
        $this->assertContains('<mdui:Logo>http://www.google.com</mdui:Logo>', $xml);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_extension_creation_at_right_position()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_leanest.xml'));

        $xml = $this->generator->generate($service);

        // @codingStandardsIgnoreStart
        $this->assertNotContains('<md:SPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:1.1:protocol urn:oasis:names:tc:SAML:2.0:protocol"><md:AssertionConsumerService', $xml);
        $this->assertContains('<md:SPSSODescriptor protocolSupportEnumeration="urn:oasis:names:tc:SAML:1.1:protocol urn:oasis:names:tc:SAML:2.0:protocol"><md:Extensions', $xml);
        // @codingStandardsIgnoreEnd

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_attributes_creation()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_leanest.xml'));

        $attr = new Attribute();
        $attr->setRequested(true);

        $service->setGivenNameAttribute($attr);

        $xml = $this->generator->generate($service);

        $this->assertContains('AttributeConsumingService index="0"', $xml);
        $this->assertContains('<md:ServiceName xml:lang="en">UNAMEEN</md:ServiceName>', $xml);

        // @codingStandardsIgnoreStart
        $this->assertContains('md:RequestedAttribute Name="urn:mace:dir:attribute-def:givenName" FriendlyName="Given name"', $xml);
        // @codingStandardsIgnoreEnd

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_logo_width_height_creation()
    {
        $logoUrl = __DIR__ . '/../fixture/image.png';

        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_leanest.xml'));
        $service->setLogoUrl($logoUrl);

        $xml = $this->generator->generate($service);

        $this->assertContains('<mdui:Logo width="1006" height="1006">' . $logoUrl . '</mdui:Logo>', $xml);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_logo_width_height_if_exists()
    {
        $logoUrl = __DIR__ . '/../fixture/image.png';

        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_lean.xml'));
        $service->setLogoUrl($logoUrl);

        $xml = $this->generator->generate($service);

        $this->assertContains('<mdui:Logo width="1006" height="1006">' . $logoUrl . '</mdui:Logo>', $xml);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_empty_logo()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_leanest.xml'));
        $service->setLogoUrl(null);

        $xml = $this->generator->generate($service);

        $this->assertNotContains('<ui:Logo', $xml);
        $this->assertNotContains('<mdui:Logo', $xml);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_empty_logo_if_exists()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_lean.xml'));
        $service->setLogoUrl(null);

        $xml = $this->generator->generate($service);

        $this->assertNotContains('<ui:Logo', $xml);
        $this->assertNotContains('<mdui:Logo', $xml);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_no_attributes()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_leanest.xml'));

        $xml = $this->generator->generate($service);

        $this->assertNotContains('<md:AttributeConsumingService', $xml);
        $this->assertNotContains('<md:ServiceName', $xml);
        $this->assertNotContains('<md:RequestedAttribute', $xml);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_no_attributes_if_exists()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_lean.xml'));

        $xml = $this->generator->generate($service);

        $this->assertNotContains('<md:AttributeConsumingService', $xml);
        $this->assertNotContains('<md:ServiceName', $xml);
        $this->assertNotContains('<md:RequestedAttribute', $xml);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function test_lean_empty_service()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_lean.xml'));

        $xml = $this->generator->generate($service);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }

    public function testLeanestEmptySubscription()
    {
        $service = $this->buildService(file_get_contents(__DIR__ . '/../fixture/metadata_leanest.xml'));

        $xml = $this->generator->generate($service);

        // Make sure the generated metadata is valid
        $this->assertInstanceOf(Metadata::class, $this->parser->parseXml($xml));
    }
}
