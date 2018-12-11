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

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityEditTest extends WebTestCase
{
    private $entityId;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        $service = $this->getServiceRepository()->findByName('SURFnet');

        $this->getAuthorizationService()->setSelectedServiceId($service->getId());

        $this->entityId = $service->getEntities()
            ->first()
            ->getId();
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/edit/{$this->entityId}");

        $form = $crawler->filter('.page-container')
            ->selectButton('Save')
            ->form();
        $nameEnfield = $form->get('dashboard_bundle_entity_type[metadata][nameEn]');
        $this->assertEquals(
            'SP1',
            $nameEnfield->getValue(),
            'Expect the NameEN field to be set with value from command'
        );
    }

    public function test_it_rejects_unauthorized_visitors()
    {
        $ibuildings = $this->getServiceRepository()->findByName('Ibuildings B.V.');
        $surfNet = $this->getServiceRepository()->findByName('SURFnet');

        $surfNetEntityId = $surfNet->getEntities()->first()->getId();

        $this->logIn('ROLE_USER', [$ibuildings]);

        $this->client->request('GET', "/entity/edit/{$surfNetEntityId}");
        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function test_it_updates_form_submissions_to_an_entity()
    {
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => 'https://www.google.com',
                    'nameEn' => 'The A Team',
                ],
                'contactInformation' => [
                    'administrativeContact' => [
                        'firstName' => 'John',
                        'lastName' => 'Doe',
                        'email' => 'john@doe.com',
                        'phone' => '999',
                    ],
                    'technicalContact' => [
                        'firstName' => 'Johnny',
                        'lastName' => 'Doe',
                        'email' => 'john@doe.com',
                        'phone' => '888',
                    ],
                    'supportContact' => [
                        'firstName' => 'Jack',
                        'lastName' => 'Doe',
                        'email' => 'john@doe.com',
                        'phone' => '777',
                    ],
                ],
                'attributes' => [
                    'givenNameAttribute' => [
                        'requested' => true,
                        'motivation' => 'We really need it!',
                    ],
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/{$this->entityId}");

        $form = $crawler
            ->selectButton('Save')
            ->form();
        $this->client->submit($form, $formData);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after editing an entity'
        );

        $entity = $this->getEntityRepository()->findById($this->entityId);

        $this->assertInstanceOf(Contact::class, $entity->getAdministrativeContact());
        $this->assertEquals('John', $entity->getAdministrativeContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $entity->getTechnicalContact());
        $this->assertEquals('Johnny', $entity->getTechnicalContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $entity->getSupportContact());
        $this->assertEquals('Jack', $entity->getSupportContact()->getFirstName());

        $givenNameAttribute = $entity->getGivenNameAttribute();
        $this->assertInstanceOf(Attribute::class, $givenNameAttribute);
        $this->assertTrue($givenNameAttribute->isRequested());
        $this->assertEquals('We really need it!', $givenNameAttribute->getMotivation());
    }

    public function test_it_loads_xml_from_url()
    {
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => 'https://engine.surfconext.nl/authentication/sp/metadata',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/{$this->entityId}");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form);
        $entity = $this->getEntityRepository()->findById($this->entityId);

        // Should have overwritten existing fields
        $this->assertEquals('DNEN', $entity->getNameEn());

        // Administrative contact is also an existing field in the fixture
        $this->assertInstanceOf(Contact::class, $entity->getAdministrativeContact());
        $this->assertEquals('Test2', $entity->getAdministrativeContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $entity->getTechnicalContact());
        $this->assertEquals('Test', $entity->getTechnicalContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $entity->getSupportContact());
        $this->assertEquals('Test3', $entity->getSupportContact()->getFirstName());

        $this->assertTrue($entity->getCommonNameAttribute()->isRequested());
        $this->assertTrue($entity->getUidAttribute()->isRequested());
        $this->assertTrue($entity->getOrganizationTypeAttribute()->isRequested());
        $this->assertTrue($entity->getAffiliationAttribute()->isRequested());
    }

    public function test_it_handles_valid_but_incomplete_metadata()
    {
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => 'https://engine.surfconext.nl/authentication/sp/metadata-valid-incomplete',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/{$this->entityId}");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form);

        $entity = $this->getEntityRepository()->findById($this->entityId);

        $this->assertEquals('DNEN', $entity->getNameEn());

        $this->assertFalse($entity->getCommonNameAttribute()->isRequested());
        $this->assertFalse($entity->getUidAttribute()->isRequested());
        $this->assertTrue($entity->getOrganizationTypeAttribute()->isRequested());
        $this->assertTrue($entity->getAffiliationAttribute()->isRequested());

        $this->assertInstanceOf(Contact::class, $entity->getTechnicalContact());
        $this->assertEquals('Test', $entity->getTechnicalContact()->getFirstName());

        $this->assertNull($entity->getSupportContact());
    }

    public function test_it_loads_xml_from_textarea()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata.xml');
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => '',
                    'pastedMetadata' => $xml,
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/{$this->entityId}");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form);

        $entity = $this->getEntityRepository()->findById($this->entityId);

        // Should have overwritten existing fields
        $this->assertEquals('DNEN', $entity->getNameEn());

        // Administrative contact is also an existing field in the fixture
        $this->assertInstanceOf(Contact::class, $entity->getAdministrativeContact());
        $this->assertEquals('Test2', $entity->getAdministrativeContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $entity->getTechnicalContact());
        $this->assertEquals('Test', $entity->getTechnicalContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $entity->getSupportContact());
        $this->assertEquals('Test3', $entity->getSupportContact()->getFirstName());

        $this->assertTrue($entity->getCommonNameAttribute()->isRequested());
        $this->assertTrue($entity->getUidAttribute()->isRequested());
        $this->assertTrue($entity->getOrganizationTypeAttribute()->isRequested());
        $this->assertTrue($entity->getAffiliationAttribute()->isRequested());

        $this->assertEquals($xml, $entity->getPastedMetadata());
    }

    public function test_it_shows_flash_message_on_exception()
    {
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => 'https://this.does.not/exist',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/{$this->entityId}");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);
        $message = $crawler->filter('.message.error')->first();

        $this->assertEquals(
            'The provided metadata is invalid.',
            trim($message->text()),
            'Expected an error message for this invalid importUrl'
        );
    }

    public function test_it_shows_flash_message_on_parse_exception()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/invalid_metadata.xml');
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => '',
                    'pastedMetadata' => $xml,
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/edit/{$this->entityId}");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);
        $message = $crawler->filter('.message.error')->first();

        $this->assertEquals(
            'The provided metadata is invalid.',
            trim($message->text()),
            'Expected an error message for this invalid importUrl'
        );

        $genericMessage = $crawler->filter('.message.preformatted')->eq(0);
        $notAllowedMessage = $crawler->filter('.message.preformatted')->eq(1);
        $missingMessage = $crawler->filter('.message.preformatted')->eq(2);

        $this->assertContains(
            "The metadata XML is invalid considering the associated XSD",
            $genericMessage->text(),
            'Expected an XML parse error.'
        );
        $this->assertContains(
            "EntityDescriptor', attribute 'entityED': The attribute 'entityED' is not allowed.",
            $notAllowedMessage->text(),
            'Expected an XML parse error.'
        );
        $this->assertContains(
            "EntityDescriptor': The attribute 'entityID' is required but missing.",
            $missingMessage->text(),
            'Expected an XML parse error.'
        );
    }
}
