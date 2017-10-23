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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;

class PublishEntityTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->markTestSkipped('The API is not ready to be consumed yet. Awaiting Okke\'s changes.');

        $service = $this->getServiceRepository()->findByName('SURFnet');
        $service->setName('test1');
        $service->setGuid('f1af6b9e-2546-4593-a57f-6ca34d2561e9');
        $service->setTeamName('team-test');
        $this->getServiceRepository()->save($service);

        $this->getAuthorizationService()->setSelectedServiceId($service->getId());

        $entity = new Entity();
        $entity->setId('a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $entity->setStatus(Entity::STATE_DRAFT);
        $entity->setSupplier($service);
        $entity->setNameEn('MyEntity');
        $entity->setMetadataXml(file_get_contents(__DIR__ . '/fixtures/publish/metadata.xml'));

        $this->getEntityRepository()->save($entity);
    }

    public function test_it_published_metadata_to_manage()
    {
        $crawler = $this->client->request('GET', '/service/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $form = $crawler
            ->selectButton('Publish')
            ->form();
        $this->client->submit($form);

        $this->assertTrue(true);
    }
}
