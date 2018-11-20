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
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntityCreateTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('Ibuildings B.V.')->getId()
        );
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/create");
        $form = $crawler->filter('.page-container')
            ->selectButton('Save')
            ->form();

        $nameEnfield = $form->get('dashboard_bundle_entity_type[metadata][nameEn]');
        $nameIdFormat = $form->get('dashboard_bundle_entity_type[metadata][nameIdFormat]');

        $this->assertEquals(
            '',
            $nameEnfield->getValue(),
            'Expect the NameEN field to be empty'
        );

        $this->assertInstanceOf(
            ChoiceFormField::class,
            $nameIdFormat,
            'Expect the NameIdFormat to be a radio group'
        );
    }
    public function test_it_imports_metadata()
    {
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => 'https://engine.surfconext.nl/authentication/sp/metadata',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/entity/create");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);

        $form = $crawler->selectButton('Publish')->form();

        // The imported metadata is loaded in the form (see: /fixtures/metadata/valid_metadata.xml)
        $this->assertEquals(
            'DNNL',
            $form->get('dashboard_bundle_entity_type[metadata][nameNl]')->getValue()
        );
        $this->assertEquals(
            'DNEN',
            $form->get('dashboard_bundle_entity_type[metadata][nameEn]')->getValue()
        );
    }

    public function test_it_can_cancel_out_of_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/create");
        $form = $crawler
            ->selectButton('Cancel')
            ->form();

        $this->client->submit($form);
        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after saving an entity'
        );

        // The entity list queries manage for published entities
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();
        $pageTitle = $crawler->filter('h1')->first()->text();
        $message = $crawler->filter('.page-container .card')->first()->text();

        $this->assertContains("Entities of service 'Ibuildings B.V.'", $pageTitle);
        $this->assertContains('There are no entities configured', $message);
    }

    public function test_it_can_save_the_form()
    {
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create");

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after saving an entity'
        );

        // The entity list queries manage for published entities
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();

        // Assert the entity id is in one of the td's of the first table row.
        $entityTr = $crawler->filter('.page-container table tbody tr')->first();
        $this->assertRegexp('/https:\/\/entity-id/', $entityTr->text());
    }

    public function test_it_can_save_the_form_without_name_id_format()
    {
        $formData = $this->buildValidFormData();
        // Unset the name id format for this test, this is the case when the SP Dashboard user fills in the form
        // manually (not using the import feature).
        unset($formData['dashboard_bundle_entity_type']['nameIdFormat']);

        $crawler = $this->client->request('GET', "/entity/create");

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after saving an entity'
        );

        // Test if the entity has got the correct default name id format by loading it from the repository
        $entities = $this->getEntityRepository()->findAll();
        /** @var Entity $entity */
        $entity = end($entities);
        $this->assertEquals(Entity::NAME_ID_FORMAT_DEFAULT, $entity->getNameIdFormat());
    }

    public function test_it_can_publish_the_form()
    {
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        // Publish json
        $this->testMockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));
        // Push to Manage
        $this->testMockHandler->append(new Response(200, [], '{"status":"OK"}'));

        $this->client->submit($form, $formData);

        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect to the published "thank you" endpoint'
        );

        // The entity list queries manage for published entities
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();
        // Publishing an entity saves and then attempts a publish to Manage, removing the entity afterwards in sp dash.
        $pageTitle = $crawler->filter('h1')->first()->text();
        $this->assertEquals('Successfully published the entity to test', $pageTitle);
    }

    public function test_it_forwards_to_edit_action_when_publish_failed()
    {
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        // Publish json
        $this->testMockHandler->append(new Response(200, [], '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}'));
        // Push to Manage
        $this->testMockHandler->append(new Response(404, [], '{"status":"failed"}'));

        $this->client->submit($form, $formData);

        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting redirect to entity edit form'
        );

        $crawler = $this->client->followRedirect();
        // Publishing an entity saves and then attempts a publish to Manage, removing the entity afterwards in sp dash.
        $pageTitle = $crawler->filter('h1')->first()->text();
        $this->assertEquals('Service Provider registration form', $pageTitle);

        $errorMessage = $crawler->filter('div.message.error')->first()->text();
        $this->assertEquals('Unable to publish the entity, try again later', trim($errorMessage));

        $uri = $this->client->getRequest()->getRequestUri();
        $this->assertRegExp('/\/entity\/edit/', $uri);
    }

    public function test_it_shows_flash_message_on_importing_invalid_metadata()
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

        $crawler = $this->client->request('GET', "/entity/create");

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

    public function test_creating_draft_for_production_is_not_allowed()
    {
        // SURFnet is not allowed to create production entities.
        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $crawler = $this->client->request('GET', '/entity/create/production');

        $this->assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function test_a_privileged_user_can_create_a_production_draft()
    {
        // Ibuildings is allowed to create production entities.
        $this->getAuthorizationService()->setSelectedServiceId(
            $this->getServiceRepository()->findByName('Ibuildings B.V.')->getId()
        );

        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', '/entity/create/production');

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $this->client->submit($form, $formData);

        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after saving an entity'
        );

        // The entity list queries manage for published entities
        $this->testMockHandler->append(new Response(200, [], '[]'));
        $this->prodMockHandler->append(new Response(200, [], '[]'));

        $crawler = $this->client->followRedirect();

        // Assert the entity is saved for the production environment.
        $row = $crawler->filter('table tbody tr')->eq(0);

        $this->assertEquals('https://entity-id', $row->filter('td')->eq(1)->text(), 'Entity ID not found in entity list');
        $this->assertEquals('production', $row->filter('td')->eq(4)->text(), 'Environment not found in entity list');
    }

    public function test_it_imports_multiple_entity_descriptor_metadata_with_a_single_entity()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata_entities_descriptor.xml');
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => '',
                    'pastedMetadata' => $xml,
                ],
            ],
        ];
        $crawler = $this->client->request('GET', "/entity/create");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);

        $form = $crawler->selectButton('Publish')->form();

        // The imported metadata is loaded in the form (see: /fixtures/metadata/valid_metadata_entities_descriptor.xml)
        $this->assertEquals(
            'FooBar: test instance',
            $form->get('dashboard_bundle_entity_type[metadata][nameEn]')->getValue()
        );
    }

    public function test_it_does_not_import_multiple_entity_descriptor_metadata_with_a_multiple_entities()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/invalid_metadata_entities_descriptor.xml');
        $formData = [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'importUrl' => '',
                    'pastedMetadata' => $xml,
                ],
            ],
        ];
        $crawler = $this->client->request('GET', "/entity/create");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);
        $notSupportedMultipleEntitiesMessage = $crawler->filter('.message.preformatted')->first();

        $this->assertContains(
            'Using metadata that describes multiple entities is not supported. Please provide metadata describing a single SP entity.',
            $notSupportedMultipleEntitiesMessage->text(),
            'Expected an error message for unsupported multiple entities in metadata.'
        );
    }
    private function buildValidFormData()
    {
        return [
            'dashboard_bundle_entity_type' => [
                'metadata' => [
                    'nameIdFormat' => Entity::NAME_ID_FORMAT_DEFAULT,
                    'descriptionNl' => 'Description NL',
                    'descriptionEn' => 'Description EN',
                    'nameEn' => 'The A Team',
                    'nameNl' => 'The A Team',
                    'metadataUrl' => 'https://metadata-url',
                    'acsLocation' => 'https://acs-location',
                    'entityId' => 'https://entity-id',
                    'certificate' => file_get_contents(__DIR__ . '/fixtures/publish/valid.cer'),
                    'logoUrl' => 'https://logo-url',
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
    }
}
