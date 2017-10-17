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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\CertificateParser;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Generator;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\JsonGenerator;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Parser;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class JsonGeneratorTest extends MockeryTestCase
{
    /**
     * @var JsonGenerator
     */
    private $generator;

    public function setup()
    {
        $this->generator = new JsonGenerator();
    }

    /**
     * @param string $metadataXml
     *
     * @return Service
     */
    private function buildService()
    {
        $service = m::mock(Service::class)->makePartial();
        $service->setNameNl('UNAMENL');
        $service->setNameEn('UNAMEEN');
        $service->setDescriptionNl('UPDATEDDESCRNL');
        $service->setDescriptionEn('UPDATEDDESCREN');
        $service->setApplicationUrl('http://www.google.nl');
        $service->setLogoUrl('http://www.google.com');
        return $service;
    }

    public function test_it_can_generate()
    {
        $service = $this->buildService();

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

        $service
            ->shouldReceive('getCreated')
            ->andReturn(new \DateTime('1910-01-01 12:00:00'));

        $json = $this->generator->generate($service);

        $this->assertContains('"name:nl":"UNAMENL",', $json);
        $this->assertContains('"name:en":"UNAMEEN",', $json);
        $this->assertContains('"description:nl":"UPDATEDDESCRNL",', $json);
        $this->assertContains('"description:en":"UPDATEDDESCREN",', $json);

    }
}
