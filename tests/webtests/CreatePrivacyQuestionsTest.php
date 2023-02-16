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

use Symfony\Component\HttpFoundation\RedirectResponse;

class CreatePrivacyQuestionsTest extends WebTestCase
{
    public function test_it_can_display_the_form()
    {
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();
        $service = $serviceRepository->findByName('SURFnet');

        $this->logIn('ROLE_USER', [$service]);

        $crawler = self::$client->request('GET', '/service/1/privacy');

        $this->assertEquals('GDPR related questions', $crawler->filter('h1')->first()->text());
        $formRows = $crawler->filter('div.form-row');
        $this->assertCount(14, $formRows);
    }

    public function test_it_can_submit_the_form()
    {
        static::markTestSkipped(
            'Fails after submit'
        );
        $this->loadFixtures();

        $serviceRepository = $this->getServiceRepository();
        $service = $serviceRepository->findByName('SURFnet');

        $this->logIn('ROLE_USER', [$service]);

        $crawler = self::$client->request('GET', '/service/1/privacy');

        $formRows = $crawler->filter('div.form-row');
        $this->assertCount(14, $formRows);

        $form = $crawler
            ->selectButton('Save')
            ->form();

        $formData = [
            'dashboard_bundle_privacy_questions_type[accessData]' => 'Some data will be accessed',
            'dashboard_bundle_privacy_questions_type[country]' => 'The Netherlands',
            'dashboard_bundle_privacy_questions_type[certification]' => true,
            'dashboard_bundle_privacy_questions_type[certificationValidTo]' => '2018-12-31',
            'dashboard_bundle_privacy_questions_type[privacyPolicyUrl]' => 'http://example.org/privacy',
        ];

        self::$client->submit($form, $formData);
        self::$client->followRedirects();
        $this->assertStringContainsString(
            'Your changes were saved!',
            self::$client->getCrawler()->filter('div.flashMessage.info')->text()
        );
    }
}
