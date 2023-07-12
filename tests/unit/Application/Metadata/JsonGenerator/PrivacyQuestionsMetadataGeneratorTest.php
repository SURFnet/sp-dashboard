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

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator\PrivacyQuestionsMetadataGenerator;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\DpaType;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class PrivacyQuestionsMetadataGeneratorTest extends MockeryTestCase
{
    protected function setUp(): void
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
        $privacyQuestions->setOtherInfo('Other information');
        $privacyQuestions->setCountry('Country');
        $privacyQuestions->setAccessData('Access data');
        $privacyQuestions->setSecurityMeasures('Measures');
        $privacyQuestions->setPrivacyStatementUrlEn('https://foobar.example.com/privacy');
        $privacyQuestions->setPrivacyStatementUrlNl('https://foobar.example.nl/privacy');
        $privacyQuestions->setDpaType('service_aqcuired_through_surf'); // DpaType::DPA_TYPE_IN_SURF_AGREEMENT

        $service->setPrivacyQuestions($privacyQuestions);
        $entity->setService($service);

        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../assets/Resources');

        $factory = new PrivacyQuestionsMetadataGenerator($metadataRepository);

        $metadata = $factory->build($entity);

        $this->assertCount(8, $metadata);

        $this->assertEquals('What data', $metadata['coin:privacy:what_data']);
        $this->assertEquals('Access data', $metadata['coin:privacy:access_data']);
        $this->assertEquals('Country', $metadata['coin:privacy:country']);
        $this->assertEquals('Measures', $metadata['coin:privacy:security_measures']);
        $this->assertEquals('Other information', $metadata['coin:privacy:other_info']);
        $this->assertEquals('https://foobar.example.com/privacy', $metadata['mdui:PrivacyStatementURL:en']);
        $this->assertEquals('https://foobar.example.nl/privacy', $metadata['mdui:PrivacyStatementURL:nl']);
        $this->assertEquals('service_aqcuired_through_surf', $metadata['coin:privacy:dpa_type']); // DpaType::DPA_TYPE_IN_SURF_AGREEMENT
    }

    public function test_it_retuns_empty_array_when_disabled()
    {
        $entity = m::mock(ManageEntity::class)->makePartial();
        $service = m::mock(Service::class)->makePartial();
        $privacyQuestions = new PrivacyQuestions();

        $service->setPrivacyQuestionsEnabled(false);
        $service->setPrivacyQuestions($privacyQuestions);

        $entity->setService($service);

        $metadataRepository = new AttributesMetadataRepository(__DIR__ . '/../../../../../assets/Resources');

        $factory = new PrivacyQuestionsMetadataGenerator($metadataRepository);
        $metadata = $factory->build($entity);

        $this->assertEmpty($metadata);
    }
}
