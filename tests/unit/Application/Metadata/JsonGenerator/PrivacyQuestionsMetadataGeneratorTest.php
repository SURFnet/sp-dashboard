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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Application\Metadata\JsonGenerator;

use DateTime;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class PrivacyQuestionsMetadataGeneratorTest extends MockeryTestCase
{
    protected function setUp()
    {
        parent::setUp();
    }

    public function test_it_can_build_privacy_question_metadata()
    {
        $entity = m::mock(ManageEntity::class)->makePartial();
        $service = m::mock(Service::class)->makePartial();
        $privacyQuestions = new PrivacyQuestions();

        $service->setPrivacyQuestionsEnabled(true);

        $privacyQuestions->setWhatData('What data');
        $privacyQuestions->setSnDpaWhyNot('We can not comply.');
        $privacyQuestions->setCertificationValidFrom(new DateTime('2018-06-04 00:00:00+02:00'));
        $privacyQuestions->setCertificationValidTo(new DateTime('2018-06-06 00:00:00+02:00'));
        $privacyQuestions->setCertification(false);
        $privacyQuestions->setCertificationLocation('https://www.google.com');
        $privacyQuestions->setPrivacyPolicy(true);
        $privacyQuestions->setOtherInfo('Other information');
        $privacyQuestions->setCountry('Country');
        $privacyQuestions->setAccessData('Access data');
        $privacyQuestions->setSurfnetDpaAgreement(true);
        $privacyQuestions->setSurfmarketDpaAgreement(false);
        $privacyQuestions->setPrivacyPolicyUrl('https://www.google.com');
        $privacyQuestions->setSecurityMeasures('Measures');

        $service->setPrivacyQuestions($privacyQuestions);
        $entity->setService($service);

        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../app/Resources');

        $factory = new PrivacyQuestionsMetadataGenerator($metadataRepository);

        $metadata = $factory->build($entity);

        $this->assertCount(14, $metadata);

        // Test some of the assertions
        $this->assertEquals('What data', $metadata['coin:privacy:what_data']);
        $this->assertFalse(is_bool($metadata['coin:privacy:certification']));
        $this->assertEquals('0', $metadata['coin:privacy:certification']);
        $this->assertEquals('2018-06-04T00:00:00+02:00', $metadata['coin:privacy:certification_valid_from']);
        $this->assertEquals('2018-06-06T00:00:00+02:00', $metadata['coin:privacy:certification_valid_to']);
    }

    public function test_it_retuns_empty_array_when_disabled()
    {
        $entity = m::mock(ManageEntity::class)->makePartial();
        $service = m::mock(Service::class)->makePartial();
        $privacyQuestions = new PrivacyQuestions();

        $service->setPrivacyQuestionsEnabled(false);
        $service->setPrivacyQuestions($privacyQuestions);

        $entity->setService($service);

        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../app/Resources');

        $factory = new PrivacyQuestionsMetadataGenerator($metadataRepository);
        $metadata = $factory->build($entity);

        $this->assertEmpty($metadata);
    }
}
