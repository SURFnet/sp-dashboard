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

namespace Surfnet\ServiceProviderDashboard\Tests\Integration\Legacy\Metadata;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository\AttributeRepository;
use Surfnet\ServiceProviderDashboard\Application\Service\AttributeService;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Parser;

class ParserTest extends MockeryTestCase
{
    /**
     * @var Parser
     */
    private $parser;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function setUp(): void
    {
        $this->logger = m::mock(LoggerInterface::class);

        $rootDir = __DIR__ . '/../../../../assets/Resources/';
        $attributeRepository = new AttributeRepository(__DIR__ . '/fixture/attributes.json');
        $this->parser = new Parser(
            new AttributeService($attributeRepository, 'en'),
            $rootDir,
            $this->logger
        );
    }

    public function test_it_can_parse_valid_metadata()
    {
        $metadata = $this->parser->parseXml(file_get_contents(__DIR__.'/fixture/metadata.xml'));

        $this->assertInstanceOf(Metadata::class, $metadata);

        $this->assertCount(3, $metadata->acsLocations);
        $this->assertEquals($metadata->acsLocations[0], 'https://domain.org/saml/sp/saml2-post/default-sp');
        $this->assertEquals($metadata->acsLocations[1], 'https://domain.org/saml/sp/saml2-post/default-sp-1');
        $this->assertEquals($metadata->acsLocations[2], 'https://domain.org/saml/sp/saml2-post/default-sp-2');

        $this->assertEquals($metadata->entityId, 'https://domain.org/saml/metadata');

        $this->assertEquals($metadata->logoUrl, 'https://LOGO.example.com/logo.png');
        $this->assertEquals($metadata->nameNl, 'DNNL');
        $this->assertEquals($metadata->nameEn, 'DNEN');
        $this->assertEquals($metadata->descriptionNl, 'DESCRNL');
        $this->assertEquals($metadata->descriptionEn, 'DESCREN');
        $this->assertEquals($metadata->applicationUrlNl, 'URLNL');
        $this->assertEquals($metadata->applicationUrlEn, 'URLEN');
        $this->assertEquals($metadata->nameIdFormat, 'urn:oasis:names:tc:SAML:1.1:nameid-format:unspecified');

        $this->assertEquals($metadata->technicalContact->getFirstName(), 'Test');
        $this->assertEquals($metadata->technicalContact->getLastName(), 'Tester');
        $this->assertEquals($metadata->technicalContact->getEmail(), 'test@domain.org');
        $this->assertEquals($metadata->technicalContact->getPhone(), '123456789');

        $this->assertEquals($metadata->administrativeContact->getFirstName(), 'Test2');
        $this->assertEquals($metadata->administrativeContact->getLastName(), 'Tester2');
        $this->assertEquals($metadata->administrativeContact->getEmail(), 'test2@domain.org');
        $this->assertEquals($metadata->administrativeContact->getPhone(), '987654321');

        $this->assertEquals($metadata->supportContact->getFirstName(), 'Test3');
        $this->assertEquals($metadata->supportContact->getLastName(), 'Tester3');
        $this->assertEquals($metadata->supportContact->getEmail(), 'test3@domain.org');
        $this->assertEquals($metadata->supportContact->getPhone(), '456789123');

        $this->assertEquals($metadata->organizationNameEn, 'orgen');
        $this->assertEquals($metadata->organizationUrlEn, 'http://orgen');
        $this->assertEquals($metadata->organizationNameNl, 'orgnl');
        $this->assertEquals($metadata->organizationUrlNl, 'http://orgnl');
        $this->assertTrue($metadata->getAttribute('emailAddressAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('displayNameAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('affiliationAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('commonNameAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('entitlementAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('givenNameAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('organizationAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('organizationTypeAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('principleNameAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('surNameAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('uidAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('preferredLanguageAttribute')->isRequested());
        $this->assertTrue($metadata->getAttribute('personalCodeAttribute')->isRequested());
    }

    public function test_it_rejects_missing_acs_metadata()
    {
        $this->expectExceptionMessage("The metadata XML is invalid considering the associated XSD");
        $this->expectException(ParserException::class);
        $this->logger->shouldReceive('error');

        $this->parser->parseXml(file_get_contents(__DIR__.'/fixture/invalid_acs_metadata.xml'));
    }

    public function test_it_rejects_to_many_acs_entries()
    {
        $this->expectExceptionMessage("The metadata should not contain an ACS with an index larger than 9.");
        $this->expectException(InvalidArgumentException::class);
        $this->logger->shouldReceive('error');
        $this->parser->parseXml(file_get_contents(__DIR__.'/fixture/invalid_index_metadata.xml'));
    }
}
