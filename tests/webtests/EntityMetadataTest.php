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
        $entity->setStatus(Entity::STATE_PUBLISHED);

        $this->getEntityRepository()->save($entity);

        $this->client->request('GET', '/entity/metadata/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $json = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals('MijnService', $json['metaDataFields']['name:nl']);
        $this->assertEquals('MyService', $json['metaDataFields']['name:en']);
        $this->assertEquals('B4AwaAYIKwYBBQUHAQEEXDBaMCsGCCsGAQUFBzAChh9odHRwOi8vcGtpLmdvb2ds', $json['metaDataFields']['certData']);
        $this->assertEquals('https://domain.org/saml/sp/saml2-post/default-sp', $json['metaDataFields']['AssertionConsumerService:0:Location']);
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
        $entity->setStatus(Entity::STATE_DRAFT);

        $this->getEntityRepository()->save($entity);

        $this->client->request('GET', '/entity/metadata/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
    }
}
