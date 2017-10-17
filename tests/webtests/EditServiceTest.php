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

use Mockery as m;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Supplier;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\SupplierRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Symfony\Component\HttpFoundation\RedirectResponse;

class EditServiceTest extends WebTestCase
{
    /**
     * @var SupplierRepository
     */
    private $supplierRepository;

    /**
     * @var ServiceRepository
     */
    private $serviceRepository;

    public function setUp()
    {
        parent::setUp();

        $this->supplierRepository = $this->client->getContainer()->get('surfnet.dashboard.repository.supplier');
        $this->supplierRepository->clear();

        $supplier = m::mock(Supplier::class)->makePartial();
        $supplier->setName('test1');
        $supplier->setGuid('f1af6b9e-2546-4593-a57f-6ca34d2561e9');
        $supplier->setTeamName('team-test');
        $supplier->shouldReceive('getId')->andReturn(1);

        $this->supplierRepository->save($supplier);

        $this->serviceRepository = $this->client->getContainer()->get('surfnet.dashboard.repository.service');
        $this->serviceRepository->clear();

        $service = new Service();
        $service->setId('a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $service->setStatus(1);
        $service->setSupplier($supplier);
        $service->setNameEn('MyEntity');
        $service->setTicketNumber('IID-9');

        $this->serviceRepository->save($service);

        $this->logIn('ROLE_ADMINISTRATOR');

        $this->client->getContainer()->get('surfnet.dashboard.service.authorization')->setAdminSwitcherSupplierId(1);
    }

    public function test_it_renders_the_form()
    {
        $crawler = $this->client->request('GET', '/service/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');
        $form = $crawler->filter('.page-container')
            ->selectButton('Save')
            ->form();
        $nameEnfield = $form->get('dashboard_bundle_edit_service_type[metadata][nameEn]');
        $this->assertEquals(
            'MyEntity',
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

        $crawler = $this->client->request('GET', '/service/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $form = $crawler
            ->selectButton('Save')
            ->form();
        $this->client->submit($form, $formData);

        $this->assertTrue(
            $this->client->getResponse() instanceof RedirectResponse,
            'Expecting a redirect response after editing a service'
        );

        /** @var Service[] $services */
        $services = $this->serviceRepository->findAll();
        $this->assertCount(1, $services);
        $service = array_pop($services);

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

        $crawler = $this->client->request('GET', '/service/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $this->client->submit($form, $formData);

        /** @var Service[] $services */
        $services = $this->serviceRepository->findAll();
        $this->assertCount(1, $services);
        $service = array_pop($services);

        // Should not have overwritten existing fields
        $this->assertEquals('MyEntity', $service->getNameEn());

        $this->assertInstanceOf(Contact::class, $service->getAdministrativeContact());
        $this->assertEquals('Test2', $service->getAdministrativeContact()->getFirstName());

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

        $crawler = $this->client->request('GET', '/service/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $this->client->submit($form, $formData);

        /** @var Service[] $services */
        $services = $this->serviceRepository->findAll();
        $this->assertCount(1, $services);
        $service = array_pop($services);

        // Explicitly not set with the value in post!
        $this->assertEquals('MyEntity', $service->getNameEn());

        $this->assertNull($service->getCommonNameAttribute());
        $this->assertNull($service->getUidAttribute());
        $this->assertTrue($service->getOrganizationTypeAttribute()->isRequested());
        $this->assertTrue($service->getAffiliationAttribute()->isRequested());

        $this->assertInstanceOf(Contact::class, $service->getTechnicalContact());
        $this->assertEquals('Test', $service->getTechnicalContact()->getFirstName());

        $this->assertNull($service->getAdministrativeContact());
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

        $crawler = $this->client->request('GET', '/service/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

        $form = $crawler
            ->selectButton('Import')
            ->form();

        $this->client->submit($form, $formData);

        /** @var Service[] $services */
        $services = $this->serviceRepository->findAll();
        $this->assertCount(1, $services);
        $service = array_pop($services);

        // Should not have overwritten existing fields
        $this->assertEquals('MyEntity', $service->getNameEn());

        $this->assertInstanceOf(Contact::class, $service->getAdministrativeContact());
        $this->assertEquals('Test2', $service->getAdministrativeContact()->getFirstName());

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

        $crawler = $this->client->request('GET', '/service/edit/a8e7cffd-0409-45c7-a37a-81bb5e7e5f66');

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
