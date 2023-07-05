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
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\DpaType;

class EditPrivacyQuestionsTest extends WebTestCase
{
    public function test_it_can_display_the_form()
    {
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();

        $this->logIn($serviceRepository->findByName('Ibuildings B.V.'));

        $crawler = self::$pantherClient->request('GET', '/service/2/privacy');

        $this->assertEquals('GDPR related questions', $crawler->filter('h1')->first()->text());
        $formRows = $crawler->filter('div.form-row');

        $this->assertCount(8, $formRows);
        $this->assertEquals('All your data are belong to us', $formRows->eq(0)->filter('textarea')->text());
        $checkedDpaType = $formRows->eq(4)->filter('input:checked')->getAttribute('value');
        $this->assertEquals(DpaType::DEFAULT, $checkedDpaType);
    }

    public function test_it_can_submit_the_form()
    {
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();

        $this->logIn($serviceRepository->findByName('Ibuildings B.V.'));

        $crawler = self::$pantherClient->request('GET', '/service/2/privacy');

        $formRows = $crawler->filter('div.form-row');
        $this->assertCount(8, $formRows);

        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="dashboard_bundle_privacy_questions_type"]'));
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_whatData', 'We will refrain from requesting any data');
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_accessData', 'Some data will be accessed');
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_country', 'The Netherlands');
        $this->checkFormField($form, '#dashboard_bundle_privacy_questions_type_dpaType_4');
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_privacyStatementUrlNl', 'foobar.example.nl/privacy');
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_privacyStatementUrlEn', 'https://foobar.example.com');
        $form->submit();

        $crawler = self::$pantherClient->refreshCrawler();

        self::assertOnPage('Your changes were saved!', $crawler);

        // Now check if the correct DpaType is displayed and the privacy statement Url was saved
        $crawler = self::$pantherClient->request('GET', '/service/2/privacy');
        $formRows = $crawler->filter('div.form-row');
        $checkedDpaType = $formRows->eq(4)->filter('input:checked')->getAttribute('value');
        $this->assertEquals('other', $checkedDpaType); // DpaType::DPA_TYPE_OTHER
        $this->assertEquals('http://foobar.example.nl/privacy', $formRows->eq(5)->filter('input')->getAttribute('value'));
        $this->assertEquals('https://foobar.example.com', $formRows->eq(6)->filter('input')->getAttribute('value'));
    }
}
