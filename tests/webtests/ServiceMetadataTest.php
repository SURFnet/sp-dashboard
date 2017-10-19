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

namespace Surfnet\ServiceProviderDashboard\Webtests;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;

class ServiceMetadataTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
    }

    /**
     * This test just checks the metadata is rendered by the ServiceMetadataController. The correct parsing of the
     * data is tested in the generator unit test.
     *
     * @see GeneratorTest
     */
    public function test_it_renders_metadata()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $service = new Service();
        $service->setId('a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $service->setAcsLocation('https://domain.org/saml/sp/saml2-post/default-sp');
        $service->setCertificate('B4AwaAYIKwYBBQUHAQEEXDBaMCsGCCsGAQUFBzAChh9odHRwOi8vcGtpLmdvb2ds');
        $service->setStatus(1);
        $service->setSupplier(
            $this->getSupplierRepository()->findByName('SURFnet')
        );
        $service->setNameEn('MyService');
        $service->setNameNl('MijnService');
        $service->setTicketNumber('IID-9');
        $service->setMetadataXml(file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata.xml'));

        $this->getServiceRepository()->save($service);

        $crawler = $this->client->request('GET', '/service/metadata/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $nodeNl = $crawler->filterXPath('//mdui:DisplayName[@xml:lang="nl"]');
        $nodeEn = $crawler->filterXPath('//mdui:DisplayName[@xml:lang="en"]');
        $certificate = $crawler->filterXPath('//ds:X509Certificate');
        $acsLocation = $crawler->filterXPath('//md:AssertionConsumerService')->first()->attr('Location');

        $this->assertContains('MijnService', $nodeNl->text());
        $this->assertContains('MyService', $nodeEn->text());
        $this->assertContains('B4AwaAYIKwYBBQUHAQEEXDBaMCsGCCsGAQUFBzAChh9odHRwOi8vcGtpLmdvb2ds', $certificate->text());
        $this->assertContains('https://domain.org/saml/sp/saml2-post/default-sp', $acsLocation);
    }

    public function test_service_must_be_out_of_draft()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $service = new Service();
        $service->setId('a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $service->setStatus(Service::STATE_DRAFT);
        $service->setSupplier(
            $this->getSupplierRepository()->findByName('SURFnet')
        );

        $this->getServiceRepository()->save($service);

        $crawler = $this->client->request('GET', '/service/metadata/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $this->assertContains(
            'Service cannot be in draft when generating the Metadata (400 Bad Request)',
            $crawler->text()
        );
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}
