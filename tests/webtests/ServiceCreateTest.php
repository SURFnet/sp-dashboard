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

use Facebook\WebDriver\WebDriverBy;
use Symfony\Component\Uid\Uuid;

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

        self::assertOnPage('This value is not a valid UUID.');
    }

    public function test_empty_institution_id_field_is_allowed_and_saves()
    {
        self::$pantherClient->request('GET', '/service/create');
        $form = self::findBy('form[name="dashboard_bundle_service_type"]');
        self::findBy('#dashboard_bundle_service_type_serviceStatus_serviceType_0')->click();
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][guid]"]', Uuid::v4()->toRfc4122());
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][name]"]', 'The A Team');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][organizationNameNl]"]', 'team-a');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[general][organizationNameEn]"]', 'team-a');
        self::fillFormField($form, 'input[name="dashboard_bundle_service_type[teams][teamManagerEmail]"]', 'loeki@example.org');
        self::findBy('#dashboard_bundle_service_type_save')->click();

        $this->assertCount(4, $this->getServiceRepository()->findAll());
        $this->assertEquals(1, $this->sendInviteRepository->count());
    }

    public function test_shows_correct_error_if_role_exists_in_invite()
    {
        $this->createRoleRepository->createRole('New Service #1 New Service #1', 'New Service #1 New Service #1', '', '', '4b0e422d-d0d0-4b9e-a521-fdd1ee5d2bad');

        $crawler = self::$pantherClient->request('GET', '/service/create');

        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="dashboard_bundle_service_type"]'));
        $this->fillFormField($form, '#dashboard_bundle_service_type_general_name', 'New Service #1');
        $this->fillFormField($form, '#dashboard_bundle_service_type_general_organizationNameNl', 'Nieuwe Service #1');
        $this->fillFormField($form, '#dashboard_bundle_service_type_general_organizationNameEn', 'New Service #1');
        $this->fillFormField($form, '#dashboard_bundle_service_type_general_guid', 'f59100e1-4232-4646-8ac5-50f3c2bc32a3');
        $this->fillFormField($form, '#dashboard_bundle_service_type_teams_teamManagerEmail', 'mail@example.org');
        $form->submit();
        $this->assertOnPage('The name "Demo role name" already exists, please use a unique name.');
    }

    public function test_shows_correct_error_if_it_cannot_send_the_role_invite_to_invite()
    {
        $crawler = self::$pantherClient->request('GET', '/service/create');

        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="dashboard_bundle_service_type"]'));
        $this->fillFormField($form, '#dashboard_bundle_service_type_general_name', 'New Service #1');
        $this->fillFormField($form, '#dashboard_bundle_service_type_general_organizationNameNl', 'Nieuwe Service #1');
        $this->fillFormField($form, '#dashboard_bundle_service_type_general_organizationNameEn', 'New Service #1');
        $this->fillFormField($form, '#dashboard_bundle_service_type_general_guid', 'f59100e1-4232-4646-8ac5-50f3c2bc32a3');
        $this->fillFormField($form, '#dashboard_bundle_service_type_teams_teamManagerEmail', 'general@failure.com');
        $form->submit();

        $this->assertOnPage('The service and role have been created. However, the creation of the admin membership failed. Please proceed to https://invite.dev.openconext.local/roles/42114');
    }

}
