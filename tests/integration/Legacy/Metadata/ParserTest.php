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
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\CertificateParser;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Parser;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

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

    public function setup()
    {
        $this->logger = m::mock(LoggerInterface::class);

        $rootDir = __DIR__.'/../../../../app/Resources/';
        $this->parser = new Parser(
            new CertificateParser(),
            new AttributesMetadataRepository($rootDir),
            $rootDir,
            $this->logger
        );
    }

    public function test_it_can_parse_valid_metadata()
    {
        $metadata = $this->parser->parseXml(file_get_contents(__DIR__.'/fixture/metadata.xml'));

        $this->assertInstanceOf(Metadata::class, $metadata);

        $this->assertEquals($metadata->acsLocation, 'https://domain.org/saml/sp/saml2-post/default-sp');
        $this->assertEquals($metadata->entityId, 'https://domain.org/saml/metadata');

        $this->assertEquals($metadata->logoUrl, 'LOGO');
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
        $this->assertEquals($metadata->organizationDisplayNameEn, 'orgdisen');
        $this->assertEquals($metadata->organizationUrlEn, 'http://orgen');
        $this->assertEquals($metadata->organizationNameNl, 'orgnl');
        $this->assertEquals($metadata->organizationDisplayNameNl, 'orgdisnl');
        $this->assertEquals($metadata->organizationUrlNl, 'http://orgnl');

        $this->assertEquals(
            $metadata->certificate,
            <<<CER
-----BEGIN CERTIFICATE-----
MIIGxTCCBa2gAwIBAgIIJa5ldegBaEAwDQYJKoZIhvcNAQEFBQAwSTELMAkGA1UE
BhMCVVMxEzARBgNVBAoTCkdvb2dsZSBJbmMxJTAjBgNVBAMTHEdvb2dsZSBJbnRl
cm5ldCBBdXRob3JpdHkgRzIwHhcNMTQxMTIwMDkyOTE0WhcNMTUwMjE4MDAwMDAw
WjBmMQswCQYDVQQGEwJVUzETMBEGA1UECAwKQ2FsaWZvcm5pYTEWMBQGA1UEBwwN
TW91bnRhaW4gVmlldzETMBEGA1UECgwKR29vZ2xlIEluYzEVMBMGA1UEAwwMKi5n
b29nbGUuY29tMFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAE7xECHjrReiXV4OZj
6K6xvYnN0j3ZOKoZNrIZ7eLMI9jSujJFLHX1tmxukBaIASzf2GX00wNbBY9AtgFs
lcoO1KOCBF0wggRZMB0GA1UdJQQWMBQGCCsGAQUFBwMBBggrBgEFBQcDAjCCAyYG
A1UdEQSCAx0wggMZggwqLmdvb2dsZS5jb22CDSouYW5kcm9pZC5jb22CFiouYXBw
ZW5naW5lLmdvb2dsZS5jb22CEiouY2xvdWQuZ29vZ2xlLmNvbYIWKi5nb29nbGUt
YW5hbHl0aWNzLmNvbYILKi5nb29nbGUuY2GCCyouZ29vZ2xlLmNsgg4qLmdvb2ds
ZS5jby5pboIOKi5nb29nbGUuY28uanCCDiouZ29vZ2xlLmNvLnVrgg8qLmdvb2ds
ZS5jb20uYXKCDyouZ29vZ2xlLmNvbS5hdYIPKi5nb29nbGUuY29tLmJygg8qLmdv
b2dsZS5jb20uY2+CDyouZ29vZ2xlLmNvbS5teIIPKi5nb29nbGUuY29tLnRygg8q
Lmdvb2dsZS5jb20udm6CCyouZ29vZ2xlLmRlggsqLmdvb2dsZS5lc4ILKi5nb29n
bGUuZnKCCyouZ29vZ2xlLmh1ggsqLmdvb2dsZS5pdIILKi5nb29nbGUubmyCCyou
Z29vZ2xlLnBsggsqLmdvb2dsZS5wdIISKi5nb29nbGVhZGFwaXMuY29tgg8qLmdv
b2dsZWFwaXMuY26CFCouZ29vZ2xlY29tbWVyY2UuY29tghEqLmdvb2dsZXZpZGVv
LmNvbYIMKi5nc3RhdGljLmNugg0qLmdzdGF0aWMuY29tggoqLmd2dDEuY29tggoq
Lmd2dDIuY29tghQqLm1ldHJpYy5nc3RhdGljLmNvbYIMKi51cmNoaW4uY29tghAq
LnVybC5nb29nbGUuY29tghYqLnlvdXR1YmUtbm9jb29raWUuY29tgg0qLnlvdXR1
YmUuY29tghYqLnlvdXR1YmVlZHVjYXRpb24uY29tggsqLnl0aW1nLmNvbYILYW5k
cm9pZC5jb22CBGcuY2+CBmdvby5nbIIUZ29vZ2xlLWFuYWx5dGljcy5jb22CCmdv
b2dsZS5jb22CEmdvb2dsZWNvbW1lcmNlLmNvbYIKdXJjaGluLmNvbYIIeW91dHUu
YmWCC3lvdXR1YmUuY29tghR5b3V0dWJlZWR1Y2F0aW9uLmNvbTALBgNVHQ8EBAMC
B4AwaAYIKwYBBQUHAQEEXDBaMCsGCCsGAQUFBzAChh9odHRwOi8vcGtpLmdvb2ds
ZS5jb20vR0lBRzIuY3J0MCsGCCsGAQUFBzABhh9odHRwOi8vY2xpZW50czEuZ29v
Z2xlLmNvbS9vY3NwMB0GA1UdDgQWBBReMq7ulPRUna/Q6eF3kzaQbpNlajAMBgNV
HRMBAf8EAjAAMB8GA1UdIwQYMBaAFErdBhYbvPZotXb1gba7Yhq6WoEvMBcGA1Ud
IAQQMA4wDAYKKwYBBAHWeQIFATAwBgNVHR8EKTAnMCWgI6Ahhh9odHRwOi8vcGtp
Lmdvb2dsZS5jb20vR0lBRzIuY3JsMA0GCSqGSIb3DQEBBQUAA4IBAQATKeJDgSxt
4Yr7eQL7cVS+LCVctHMwspM9fzaPIV3aph70oVQ6x7n28CJn/uSm3ASsWklcN4fC
yDSybBRYSp2lHcWX6gAt32OLTAi94/3hJP7jC0k/oG8880mZbM5BvywIUmko19nz
OKwmkZTeHgq09wVw5soF9l+orXv5lymml+NNb5A5v+F5jNZdVwaEdxwe6LjXkpYy
95e/buZ+bcHfYkU2xMH9WAPa62U3Fhr+j1xBm8hC2fS1DNOSbvBspKxAbuG7I0LQ
YWjtV5KlAn7Pqug4IgbM+mSsJ2t0u8kwlW0e+/7J789I5HiYkNpEr2ya6F/NZi4x
oLtcUeUILPXB
-----END CERTIFICATE-----
CER
        );

        $this->assertTrue($metadata->emailAddressAttribute->isRequested());
        $this->assertTrue($metadata->displayNameAttribute->isRequested());
        $this->assertTrue($metadata->affiliationAttribute->isRequested());
        $this->assertTrue($metadata->commonNameAttribute->isRequested());
        $this->assertTrue($metadata->entitlementAttribute->isRequested());
        $this->assertTrue($metadata->givenNameAttribute->isRequested());
        $this->assertTrue($metadata->organizationAttribute->isRequested());
        $this->assertTrue($metadata->organizationTypeAttribute->isRequested());
        $this->assertTrue($metadata->principleNameAttribute->isRequested());
        $this->assertTrue($metadata->surNameAttribute->isRequested());
        $this->assertTrue($metadata->uidAttribute->isRequested());
        $this->assertTrue($metadata->preferredLanguageAttribute->isRequested());
        $this->assertTrue($metadata->personalCodeAttribute->isRequested());
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException
     * @expectedExceptionMessage The metadata XML is invalid considering the associated XSD
     */
    public function test_it_rejects_missing_acs_metadata()
    {
        $this->logger->shouldReceive('error');

        $this->parser->parseXml(file_get_contents(__DIR__.'/fixture/invalid_acs_metadata.xml'));
    }

    /**
     * @expectedException \Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException
     * @expectedExceptionMessage The metadata should not contain an ACS with an index larger than 9.
     */
    public function test_it_rejects_to_many_acs_entries()
    {
        $this->logger->shouldReceive('error');
        $this->parser->parseXml(file_get_contents(__DIR__.'/fixture/invalid_index_metadata.xml'));
    }
}
