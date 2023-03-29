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

use Ramsey\Uuid\Uuid;

class ServiceCreateTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->teamsQueryClient->registerTeam('demo:openconext:org:surf.nl', 'data');
        $this->logIn();
    }

    public function test_it_validates_the_form()
    {
        self::$pantherClient->request('GET', '/service/create');
        $form = self::findBy('form[name="dashboard_bundle_service_type"]');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][guid]"]', '-bffd-xz-874a-b9f4fdf92942');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][name]"]', 'The A Team');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][organizationNameNl]"]', 'team-a');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][organizationNameEn]"]', 'team-a');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[teams][teamManagerEmail]"]', 'loeki@example.org');
        self::findBy('#dashboard_bundle_service_type_save')->click();

        self::assertOnPage('This is not a valid UUID.');
    }

    public function test_empty_institution_id_field_is_allowed()
    {
        self::$pantherClient->request('GET', '/service/create');
        $form = self::findBy('form[name="dashboard_bundle_service_type"]');
        self::findBy('#dashboard_bundle_service_type_serviceStatus_serviceType_0')->click();
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][guid]"]', Uuid::uuid4()->toString());
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][name]"]', 'The A Team');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][organizationNameNl]"]', 'team-a');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][organizationNameEn]"]', 'team-a');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[teams][teamManagerEmail]"]', 'loeki@example.org');
        self::findBy('#dashboard_bundle_service_type_save')->click();
        $services = $this->getServiceRepository()->findAll();
        $this->assertCount(3, $services);
    }
}
