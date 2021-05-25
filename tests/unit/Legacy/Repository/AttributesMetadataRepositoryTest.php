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

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Generator;
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
          "organizationUnit",
          "surName",
          "givenName",
          "entitlement",
          "uid",
          "principleName",
          "preferredLanguage",
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

    public function test_it_has_all_metadata_attribute_urns()
    {
        $expectedAttributes = [
            'urn:mace:dir:attribute-def:displayName',
            'urn:mace:dir:attribute-def:eduPersonAffiliation',
            'urn:mace:dir:attribute-def:eduPersonScopedAffiliation',
            'urn:mace:dir:attribute-def:mail',
            'urn:mace:dir:attribute-def:cn',
            'urn:mace:terena.org:attribute-def:schacHomeOrganization',
            'urn:mace:terena.org:attribute-def:schacHomeOrganizationType',
            "urn:mace:dir:attribute-def:ou",
            'urn:mace:dir:attribute-def:sn',
            'urn:mace:dir:attribute-def:givenName',
            'urn:mace:dir:attribute-def:eduPersonEntitlement',
            'urn:mace:dir:attribute-def:uid',
            'urn:mace:dir:attribute-def:eduPersonPrincipalName',
            'urn:mace:dir:attribute-def:preferredLanguage',
            'urn:schac:attribute-def:schacPersonalUniqueCode',
            'urn:mace:dir:attribute-def:eduPersonTargetedID',
        ];

        $urns = $this->repository->findAllAttributeUrns();

        $this->assertCount(16, $urns);
        $this->assertEquals($expectedAttributes, $urns);
    }
}
