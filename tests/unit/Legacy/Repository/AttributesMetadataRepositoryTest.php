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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Legacy\Metadata;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Attribute;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Metadata;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\CertificateParser;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Generator;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Parser;
use Surfnet\ServiceProviderDashboard\Legacy\Repository\AttributesMetadataRepository;

class AttributesMetadataRepositoryTest extends MockeryTestCase
{
    /**
     * @var AttributesMetadataRepository
     */
    private $repository;

    public function setUp()
    {
        $this->repository = new AttributesMetadataRepository(__DIR__ . '/../../../../app/Resources/');
        parent::setUp();
    }

    public function test_it_has_all_metadata_attributes()
    {
        $expectedAttributes = [
          "displayName",
          "affiliation",
          "scopedaffiliation",
          "emailAddress",
          "commonName",
          "organization",
          "organizationType",
          "surName",
          "givenName",
          "entitlement",
          "uid",
          "principleName",
          "preferredLanguage",
          "isMemberOf",
          "personalCode",
          "eduPersonTargetedID",
        ];

        $attributes = $this->repository->findAll();

        $this->assertCount(16, $attributes);
        foreach ($attributes as $attribute) {
            $this->assertContains($attribute->id, $expectedAttributes);
        }
    }

    public function test_it_has_all_privacy_question_attributes()
    {
        $expectedAttributes = [
            "whatData",
            "accessData",
            "country",
            "securityMeasures",
            "certification",
            "certificationLocation",
            "certificationValidFrom",
            "certificationValidTo",
            "surfmarketDpaAgreement",
            "surfnetDpaAgreement",
            "snDpaWhyNot",
            "privacyPolicy",
            "privacyPolicyUrl",
            "otherInfo",
        ];

        $attributes = $this->repository->findAllPrivacyQuestionsAttributes();

        $this->assertCount(14, $attributes);
        foreach ($attributes as $attribute) {
            $this->assertContains($attribute->id, $expectedAttributes);
        }
    }

    public function test_it_has_all_metadata_motivation_attributes()
    {
        $expectedAttributes = [
            "eduPersonTargetedIDMotivation",
            "eduPersonPrincipalNameMotivation",
            "displayNameMotivation",
            "cnMotivation",
            "givenNameMotivation",
            "snMotivation",
            "mailMotivation",
            "schacHomeOrganizationMotivation",
            "schacHomeOrganizationTypeMotivation",
            "schacPersonalUniqueCodeMotivation",
            "eduPersonAffiliationMotivation",
            "eduPersonScopedAffiliationMotivation",
            "eduPersonEntitlementMotivation",
            "eduPersonOrcidMotivation",
            "isMemberOfMotivation",
            "uidMotivation",
            "preferredLanguageMotivation",
        ];

        $attributes = $this->repository->findAllMotivationAttributes();

        $this->assertCount(17, $attributes);
        foreach ($attributes as $attribute) {
            $this->assertContains($attribute->id, $expectedAttributes);
        }
    }

    public function test_it_has_all_sp_dashboard_attributes()
    {
        $expectedAttributes = [
            "eula",
            "applicationUrl",
            "teamID",
            "originalMetadataUrl",
        ];

        $attributes = $this->repository->findAllSpDashboardAttributes();

        $this->assertCount(4, $attributes);
        foreach ($attributes as $attribute) {
            $this->assertContains($attribute->id, $expectedAttributes);
        }
    }
}
