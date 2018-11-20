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

use GuzzleHttp\Psr7\Response;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityPublishToTestTest extends WebTestCase
{
    public function setUp($loadFixtures = true)
    {
        parent::setUp();

        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();

        $surfNet = $serviceRepository->findByName('SURFnet');

        $this->logIn(
            'ROLE_USER',
            [
                $surfNet,
            ]
        );

        $this->getAuthorizationService()->setSelectedServiceId(
            $surfNet->getId()
        );
    }

    private function buildEntityWithAttribute(Service $service)
    {
        $entity = $this->buildEntityWithoutAttribute($service);

        $givenName = new Attribute();
        $givenName->setRequested(true);
        $givenName->setMotivation('test');

        $entity->setGivenNameAttribute($givenName);

        return $entity;
    }

    private function buildEntityWithoutAttribute(Service $service)
    {
        $entity = new Entity();
        $entity->setId('a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $entity->setEntityId('https://domain.org/saml/sp/saml2-post/default-sp/metadata');
        $entity->setMetadataUrl('https://domain.org/saml/sp/saml2-post/default-sp/metadata');
        $entity->setAcsLocation('https://domain.org/saml/sp/saml2-post/default-sp/acs');
        $entity->setCertificate(file_get_contents(__DIR__ . '/fixtures/publish/valid.cer'));
        $entity->setLogoUrl('http://localhost/img.png');
        $entity->setService($service);
        $entity->setNameEn('MyService');
        $entity->setNameNl('MijnService');
        $entity->setDescriptionEn('MijnService description');
        $entity->setDescriptionNl('MijnService description');
        $entity->setTicketNumber('IID-9');
        $entity->setTechnicalContact($this->buildContact());
        $entity->setEnvironment(Entity::ENVIRONMENT_TEST);
        $entity->setMetadataXml(file_get_contents(__DIR__ . '/fixtures/publish/metadata.xml'));

        return $entity;
    }

    private function buildContact($firstName = 'John', $lastName = 'Doe', $email = 'foobar@exaple.com', $phone = null)
    {
        $contact = new Contact();
        $contact
            ->setFirstName($firstName)
            ->setLastName($lastName)
            ->setEmail($email)
            ->setPhone($phone);
        return $contact;
    }

    public function test_it_published_metadata_to_manage()
    {
        // Entity id validation
        $this->testMockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));
        // Push to Manage
        $this->testMockHandler->append(new Response(200, [], '{"status":"OK"}'));

        // Build and save an entity to work with
        $entity = $this->buildEntityWithAttribute($this->getServiceRepository()->findByName('SURFnet'));
        $this->getEntityRepository()->save($entity);

        $crawler = $this->client->request('GET', '/entity/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $this->client->submit($form);
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after selecting a service'
        );

        $crawler = $this->client->followRedirect();

        $selectedService = $crawler->filter('div.card')->first();

        $this->assertEquals(
            'Thanks for publishing "MyService" to our test environment.',
            trim($selectedService->text()),
            "Publishing thank you text should be displayed"
        );

        // After publishing the entity, it should no longer be accessible
        $this->client->request('GET', '/entity/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_validates_the_from_on_publish()
    {
        // Entity id validation
        $this->testMockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));

        $entity = $this->buildEntityWithAttribute($this->getServiceRepository()->findByName('SURFnet'));
        $entity->setCertificate('-----BEGIN CERTIFICATE-----THIS IS NOT A VALID CERTIFICATE-----END CERTIFICATE-----');
        $this->getEntityRepository()->save($entity);

        $crawler = $this->client->request('GET', '/entity/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $crawler = $this->client->submit($form);

        $errors = $crawler->filter('div.form-row.error');
        $error = $errors->first()->filter('li.error')->first()->text();

        $this->assertCount(1, $errors);
        $this->assertEquals('The certificate is not valid.', $error);
    }

    public function test_it_validates_at_least_one_attribute_present()
    {
        $this->testMockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));

        $entity = $this->buildEntityWithoutAttribute($this->getServiceRepository()->findByName('SURFnet'));

        $this->getEntityRepository()->save($entity);

        $crawler = $this->client->request('GET', '/entity/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $crawler = $this->client->submit($form);

        $errors = $crawler->filter('li.error');
        $error = $errors->first()->text();

        $this->assertCount(1, $errors);
        $this->assertEquals('At least one attribute must be enabled.', $error);
    }
}
