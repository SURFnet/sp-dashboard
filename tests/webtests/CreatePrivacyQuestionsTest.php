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

class CreatePrivacyQuestionsTest extends WebTestCase
{
    public function test_it_can_display_the_form()
    {
        $this->loadFixtures();
        $serviceRepository = $this->getServiceRepository();
        $this->logIn($serviceRepository->findByName('SURFnet'));
        $crawler = self::$pantherClient->request('GET', '/service/1/privacy');

        $this->assertEquals('GDPR related questions', $crawler->filter('h1')->first()->text());

        $formRows = $crawler->filter('div.form-row');

        $this->assertCount(8, $formRows);
    }

    public function test_it_can_submit_the_form()
    {
        $this->loadFixtures();
        $serviceRepository = $this->getServiceRepository();
        $this->logIn($serviceRepository->findByName('SURFnet'));
        $crawler = self::$pantherClient->request('GET', '/service/1/privacy');
        $formRows = $crawler->filter('div.form-row');

        $this->assertCount(8, $formRows);

        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="dashboard_bundle_privacy_questions_type"]'));
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_whatData', 'We will refrain from requesting any data');
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_accessData', 'Some data will be accessed');
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_country', 'The Netherlands');
        $this->checkFormField($form, '#dashboard_bundle_privacy_questions_type_dpaType_2');
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_privacyStatementUrlNl', 'foobar.example.nl/privacy');
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_privacyStatementUrlEn', 'https://foobar.example.com');
        $form->submit();

        $crawler = self::$pantherClient->refreshCrawler();

        self::assertOnPage('Your changes were saved!', $crawler);
    }
    public function test_it_rejects_invalid_privacy_statement_urls()
    {
        $this->loadFixtures();
        $serviceRepository = $this->getServiceRepository();
        $this->logIn($serviceRepository->findByName('SURFnet'));
        $crawler = self::$pantherClient->request('GET', '/service/1/privacy');
        $formRows = $crawler->filter('div.form-row');

        $this->assertCount(8, $formRows);

        $form = $crawler->findElement(WebDriverBy::cssSelector('form[name="dashboard_bundle_privacy_questions_type"]'));
        $this->fillFormField($form, '#dashboard_bundle_privacy_questions_type_privacyStatementUrlEn', 'I dont know if we have a privacy policy url.');
        $form->submit();
        $crawler = self::$pantherClient->refreshCrawler();

        self::assertOnPage('This value is not a valid URL.', $crawler);
    }
}
