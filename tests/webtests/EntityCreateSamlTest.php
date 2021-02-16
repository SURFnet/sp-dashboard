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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Symfony\Component\DomCrawler\Field\ChoiceFormField;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EntitySamlCreateSamlTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        $this->switchToService('Ibuildings B.V.');
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/entity/create/2/saml20/test");
        $form = $crawler->filter('.page-container')
            ->selectButton('Publish')
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

        $crawler = $this->client->request('GET', "/entity/create/2/saml20/test");

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
        $crawler = $this->client->request('GET', "/entity/create/2/saml20/test");
        $form = $crawler
            ->selectButton('Cancel')
            ->form();

        $this->client->submit($form);
        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after saving an entity'
        );

        $crawler = $this->client->followRedirect();
        $pageTitle = $crawler->filter('.service-title')->first()->text();
        $messageTest = $crawler->filter('.no-entities-test')->text();
        $messageProduction = $crawler->filter('.no-entities-production')->text();

        $this->assertContains("Ibuildings B.V. overview", $pageTitle);
        $this->assertContains('No entities found.', $messageTest);
        $this->assertContains('No entities found.', $messageProduction);
    }

    public function test_it_can_publish_the_form()
    {
        $this->testPublicationClient->registerPublishResponse('https://entity-id', '{"id":"f1e394b2-08b1-4882-8b32-43876c15c743"}');
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create/2/saml20/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $this->client->submit($form, $formData);

        // The form is now redirected to the list view
        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect to the published "thank you" endpoint'
        );

        $crawler = $this->client->followRedirect();
        $pageTitle = $crawler->filter('h1')->first()->text();
        $this->assertEquals('Successfully published the entity to test', $pageTitle);
    }

    public function test_it_stays_on_create_action_when_publish_failed()
    {
        // Register an enitty with the same entity id.
        $this->registerManageEntity(
            'test',
            'oidc10_rp',
            'f1e394b2-08b1-4882-8b32-43876c15c743',
            'Existing SP',
            'https://entity-id'
        );
        $formData = $this->buildValidFormData();

        $crawler = $this->client->request('GET', "/entity/create/2/saml20/test");

        $form = $crawler
            ->selectButton('Publish')
            ->form();

        $crawler = $this->client->submit($form, $formData);
        $pageTitle = $crawler->filter('h1')->first()->text();
        $this->assertEquals('Service Provider registration form', $pageTitle);

        $errorMessage = $crawler->filter('div.message.error')->first()->text();
        $this->assertEquals('Warning! Some entries are missing or incorrect. Please review and fix those entries below.', trim($errorMessage));

        $uri = $this->client->getRequest()->getRequestUri();
        $this->assertRegExp('/\/entity\/create/', $uri);
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

        $crawler = $this->client->request('GET', "/entity/create/2/saml20/test");

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
        $this->getAuthorizationService()->changeActiveService(
            $this->getServiceRepository()->findByName('SURFnet')->getId()
        );

        $this->client->request('GET', '/entity/create/1/saml20production');

        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
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
        $crawler = $this->client->request('GET', "/entity/create/2/saml20/test");

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
        $crawler = $this->client->request('GET', "/entity/create/2/saml20/test");

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
