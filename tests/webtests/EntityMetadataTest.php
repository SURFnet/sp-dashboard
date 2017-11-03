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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

class EntityMetadataTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();
    }

    /**
     * This test just checks the metadata is rendered by the EntityMetadataController. The correct parsing of the
     * data is tested in the generator unit test.
     *
     * @see GeneratorTest
     */
    public function test_it_renders_metadata()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $entity = new Entity();
        $entity->setId('a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $entity->setAcsLocation('https://domain.org/saml/sp/saml2-post/default-sp');
        $entity->setCertificate('B4AwaAYIKwYBBQUHAQEEXDBaMCsGCCsGAQUFBzAChh9odHRwOi8vcGtpLmdvb2ds');
        $entity->setService(
            $this->getServiceRepository()->findByName('SURFnet')
        );
        $entity->setNameEn('MyService');
        $entity->setNameNl('MijnService');
        $entity->setTicketNumber('IID-9');
        $entity->setStatus(Entity::STATE_PUBLISHED);
        $entity->setMetadataXml(file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata.xml'));

        $this->getEntityRepository()->save($entity);

        $crawler = $this->client->request('GET', '/entity/metadata/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $nodeNl = $crawler->filterXPath('//mdui:DisplayName[@xml:lang="nl"]');
        $nodeEn = $crawler->filterXPath('//mdui:DisplayName[@xml:lang="en"]');
        $certificate = $crawler->filterXPath('//ds:X509Certificate');
        $acsLocation = $crawler->filterXPath('//md:AssertionConsumerService')->first()->attr('Location');

        $this->assertContains('MijnService', $nodeNl->text());
        $this->assertContains('MyService', $nodeEn->text());
        $this->assertContains('B4AwaAYIKwYBBQUHAQEEXDBaMCsGCCsGAQUFBzAChh9odHRwOi8vcGtpLmdvb2ds', $certificate->text());
        $this->assertContains('https://domain.org/saml/sp/saml2-post/default-sp', $acsLocation);
    }

    public function test_it_only_shows_published_metadata()
    {
        $this->logIn('ROLE_ADMINISTRATOR');

        $entity = new Entity();
        $entity->setId('a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $entity->setAcsLocation('https://domain.org/saml/sp/saml2-post/default-sp');
        $entity->setCertificate('B4AwaAYIKwYBBQUHAQEEXDBaMCsGCCsGAQUFBzAChh9odHRwOi8vcGtpLmdvb2ds');
        $entity->setService(
            $this->getServiceRepository()->findByName('SURFnet')
        );
        $entity->setNameEn('MyService');
        $entity->setNameNl('MijnService');
        $entity->setTicketNumber('IID-9');
        $entity->setMetadataXml(file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata.xml'));
        $entity->setStatus(Entity::STATE_DRAFT);

        $this->getEntityRepository()->save($entity);

        $this->client->request('GET', '/entity/metadata/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}
