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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EditServiceTest extends WebTestCase
{
    private $serviceId;

    public function setUp()
    {
        parent::setUp();

        $this->loadFixtures();

        $this->logIn('ROLE_ADMINISTRATOR');

        $supplier = $this->getSupplierRepository()->findByName('SURFnet');

        $this->getAuthorizationService()->setAdminSwitcherSupplierId($supplier->getId());

        $this->serviceId = $supplier->getServices()
            ->first()
            ->getId();
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', "/service/edit/{$this->serviceId}");
        $form = $crawler->filter('.page-container')
            ->selectButton('Save')
            ->form();
        $nameEnfield = $form->get('dashboard_bundle_edit_service_type[metadata][nameEn]');
        $this->assertEquals(
            'SP1',
            $nameEnfield->getValue(),
            'Expect the NameEN field to be set with value from command'
        );
    }

    public function test_it_updates_form_submissions_to_a_service()
    {
        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'general' => [
                    'environment' => 'connect',
                    'janusId' => '123321',
                ],
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

        $crawler = $this->client->request('GET', "/service/edit/{$this->serviceId}");

        $form = $crawler
            ->selectButton('Save')
            ->form();
        $this->client->submit($form, $formData);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after editing a service'
        );

        $service = $this->getServiceRepository()->findById($this->serviceId);

        $this->assertInstanceOf(Contact::class, $service->getAdministrativeContact());
        $this->assertEquals('John', $service->getAdministrativeContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $service->getTechnicalContact());
        $this->assertEquals('Johnny', $service->getTechnicalContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $service->getSupportContact());
        $this->assertEquals('Jack', $service->getSupportContact()->getFirstName());

        $givenNameAttribute = $service->getGivenNameAttribute();
        $this->assertInstanceOf(Attribute::class, $givenNameAttribute);
        $this->assertTrue($givenNameAttribute->isRequested());
        $this->assertEquals('We really need it!', $givenNameAttribute->getMotivation());
    }

    public function test_it_loads_xml_from_url()
    {
        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'general' => [
                    'environment' => 'connect',
                    'janusId' => '123321',
                ],
                'metadata' => [
                    'importUrl' => 'https://engine.surfconext.nl/authentication/sp/metadata',
                    'nameEn' => 'The A Team',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/service/edit/{$this->serviceId}");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $this->client->submit($form, $formData);

        $service = $this->getServiceRepository()->findById($this->serviceId);

        // Should not have overwritten existing fields
        $this->assertEquals('SP1', $service->getNameEn());

        // Administrative contact is also an existing field in the fixture
        $this->assertInstanceOf(Contact::class, $service->getAdministrativeContact());
        $this->assertEquals('John', $service->getAdministrativeContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $service->getTechnicalContact());
        $this->assertEquals('Test', $service->getTechnicalContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $service->getSupportContact());
        $this->assertEquals('Test3', $service->getSupportContact()->getFirstName());

        $this->assertTrue($service->getCommonNameAttribute()->isRequested());
        $this->assertTrue($service->getUidAttribute()->isRequested());
        $this->assertTrue($service->getOrganizationTypeAttribute()->isRequested());
        $this->assertTrue($service->getAffiliationAttribute()->isRequested());

        $expectedXml = file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata.xml');
        $this->assertEquals($expectedXml, $service->getMetadataXml());
    }

    public function test_it_handles_valid_but_incomplete_metadata()
    {
        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'metadata' => [
                    'importUrl' => 'https://engine.surfconext.nl/authentication/sp/metadata-valid-incomplete',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/service/edit/{$this->serviceId}");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $this->client->submit($form, $formData);

        $service = $this->getServiceRepository()->findById($this->serviceId);

        // Explicitly not set with the value in post!
        $this->assertEquals('SP1', $service->getNameEn());

        $this->assertNull($service->getCommonNameAttribute());
        $this->assertNull($service->getUidAttribute());
        $this->assertTrue($service->getOrganizationTypeAttribute()->isRequested());
        $this->assertTrue($service->getAffiliationAttribute()->isRequested());

        $this->assertInstanceOf(Contact::class, $service->getTechnicalContact());
        $this->assertEquals('Test', $service->getTechnicalContact()->getFirstName());

        $this->assertNull($service->getSupportContact());
    }

    public function test_it_loads_xml_from_textarea()
    {
        $xml = file_get_contents(__DIR__ . '/fixtures/metadata/valid_metadata.xml');
        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'metadata' => [
                    'importUrl' => '',
                    'pastedMetadata' => $xml,
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/service/edit/{$this->serviceId}");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $this->client->submit($form, $formData);

        $service = $this->getServiceRepository()->findById($this->serviceId);

        // Should not have overwritten existing fields
        $this->assertEquals('SP1', $service->getNameEn());

        // Administrative contact is also an existing field in the fixture
        $this->assertInstanceOf(Contact::class, $service->getAdministrativeContact());
        $this->assertEquals('John', $service->getAdministrativeContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $service->getTechnicalContact());
        $this->assertEquals('Test', $service->getTechnicalContact()->getFirstName());

        $this->assertInstanceOf(Contact::class, $service->getSupportContact());
        $this->assertEquals('Test3', $service->getSupportContact()->getFirstName());

        $this->assertTrue($service->getCommonNameAttribute()->isRequested());
        $this->assertTrue($service->getUidAttribute()->isRequested());
        $this->assertTrue($service->getOrganizationTypeAttribute()->isRequested());
        $this->assertTrue($service->getAffiliationAttribute()->isRequested());

        $this->assertEquals($xml, $service->getPastedMetadata());
        $this->assertEquals($xml, $service->getMetadataXml());
    }

    public function test_it_shows_flash_message_on_exception()
    {
        $formData = [
            'dashboard_bundle_edit_service_type' => [
                'metadata' => [
                    'importUrl' => 'https://this.does.not/exist',
                ],
            ],
        ];

        $crawler = $this->client->request('GET', "/service/edit/{$this->serviceId}");

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $crawler = $this->client->submit($form, $formData);
        $message = $crawler->filter('.message.error')->first();

        $this->assertEquals(
            'The metadata XML is invalid considering the associated XSD',
            trim($message->text()),
            'Expected an error message for this invalid importUrl'
        );
    }
}
