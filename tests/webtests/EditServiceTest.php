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
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DataFixtures\ORM\WebTestFixtures;

class EditServiceTest extends WebTestCase
{
    public function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures();
        $this->teamsQueryClient->registerTeam('demo:openconext:org:team-a', '{"teamId": 3}');
    }

    public function test_can_edit_existing_service()
    {
        $this->logIn();
        $this->switchToService('SURFnet');
        $button = self::findBy('.service-status-title-button');
        $button->click();

        $crawler = self::$pantherClient->refreshCrawler();

        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="dashboard_bundle_edit_service_type"]'));
        $this->fillFormField($form, '#dashboard_bundle_edit_service_type_general_guid', 'f1af6b9e-2546-4593-a57f-6ca34d2561e9');
        $this->fillFormField($form, '#dashboard_bundle_edit_service_type_general_name', 'The A Team');
        $this->fillFormField($form, '#dashboard_bundle_edit_service_type_general_organizationNameNl', 'Groepje A');
        $this->checkFormField($form, '#dashboard_bundle_edit_service_type_serviceStatus_surfconextRepresentativeApproved_1');
        $this->fillFormField($form, '#dashboard_bundle_edit_service_type_teams_teamName', 'urn:collab:group:vm.openconext.org:demo:openconext:org:team-a');
        self::$pantherClient->executeScript("document.getElementsByClassName('service-form').item(0).submit();");
        self::assertOnPage('Your changes were saved!');
    }

    /**
     * Admins can toggle the privacy question feature for Services. Effectively enabling/disabling the Privacy
     * question form.
     */
    public function test_privacy_questions_admin_toggle()
    {
        $serviceRepository = $this->getServiceRepository();

        $this->logIn();
        $this->switchToService('SURFnet');

        $crawler = self::$pantherClient->request('GET', '/service/1/edit');

        // Step 1: Admin sets privacy questions enabled to false
        $formData = [
            'dashboard_bundle_edit_service_type[general][privacyQuestionsEnabled]' => false,
            'dashboard_bundle_edit_service_type[teams][teamName]' => WebTestFixtures::TEAMNAME_SURF,
            'dashboard_bundle_edit_service_type[serviceStatus][surfconextRepresentativeApproved]' => 'no',
        ];
        $form = $crawler
            ->selectButton('Save')
            ->form();
        $form->setValues($formData);
        self::$pantherClient->executeScript("document.getElementsByClassName('service-form').item(0).submit();");
        self::$pantherClient->wait(3);
        self::assertOnPage('Your changes were saved!');

        // Step 2: Surfnet can't access the privacy questions
        $this->logOut();
        $surfNet = $serviceRepository->findByName('SURFnet');
        $this->logIn($surfNet);

        self::$pantherClient->request('GET', '/service/2/edit');

        self::assertOnPage('Access denied');
    }
}
