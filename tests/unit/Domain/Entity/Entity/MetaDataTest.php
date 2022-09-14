<?php

/**
 * Copyright 2020 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Domain\Entity\Entity;

use Mockery as m;
use PHPUnit\Framework\TestCase;
use Surfnet\ServiceProviderDashboard\Application\Metadata\AcsLocationHelper;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\ContactList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Organization;

class MetaDataTest extends TestCase
{
    public function test_it_can_load_asc_locations()
    {
        $data['data'] = json_decode(file_get_contents(__DIR__.'/fixture/read_response.json'), true);
        $metadata = MetaData::fromApiResponse($data);
        $this->assertCount(3, $metadata->getAcsLocations());
    }

    public function test_it_throws_exception_on_invalid_acs_location()
    {
        $data['data'] = json_decode(file_get_contents(__DIR__.'/fixture/read_response_invalid_acs_location.json'), true);
        $this->expectExceptionMessage('Expected a string. Got: integer');
        MetaData::fromApiResponse($data);
    }

    public function test_it_throws_exception_on_double_acs_locations()
    {
        $data['data'] = json_decode(file_get_contents(__DIR__.'/fixture/read_response_double_acs_location.json'), true);
        $this->expectExceptionMessage('Double acs locations. Expected unique locations');
        MetaData::fromApiResponse($data);
    }

    public function test_it_exceeds_max_asc_locations()
    {
        $data['data'] = json_decode(file_get_contents(__DIR__.'/fixture/response_json_exceeds_max_acs_locations.json'), true);
        $this->expectExceptionMessage('Maximum acs locations exceeded. Maximum '.MetaData::MAX_ACS_LOCATIONS.' acs location are supported');
        MetaData::fromApiResponse($data);
    }

    public function test_it_adds_acs_location_to_the_meta_data()
    {
        $metadata = [];
        AcsLocationHelper::addAcsLocationsToMetaData(['https://example1.com'], $metadata);
        $this->assertEquals(
            'urn:oasis:names:tc:SAML:2.0:bindings:HTTP-POST',
            $metadata['AssertionConsumerService:0:Binding']
        );
        $this->assertEquals(
            'https://example1.com',
            $metadata['AssertionConsumerService:0:Location']
        );
    }

    public function test_it_adds_acs_locations_to_the_meta_data()
    {
        $metadata = [];
        AcsLocationHelper::addAcsLocationsToMetaData(['https://example1.com', 'https://example2.com' ], $metadata);
        $this->assertCount(4, $metadata);
        $this->assertEquals(
            'https://example1.com',
            $metadata['AssertionConsumerService:0:Location']
        );
        $this->assertEquals(
            'https://example2.com',
            $metadata['AssertionConsumerService:1:Location']
        );
    }

    public function test_it_adds_empty_acs_locations()
    {
        $metadata = [];
        AcsLocationHelper::addEmptyAscLocationsToMetaData([], $metadata);
        $this->assertCount(20, $metadata);
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:0:Location']
        );
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:1:Location']
        );
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:2:Location']
        );
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:3:Location']
        );
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:4:Location']
        );
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:5:Location']
        );
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:6:Location']
        );
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:7:Location']
        );
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:8:Location']
        );
        $this->assertEquals(
            null,
            $metadata['AssertionConsumerService:9:Location']
        );
    }

    public function test_it_adds_consecutive_acs_locations()
    {
        $metadata = [];
        AcsLocationHelper::addAcsLocationsToMetaData([null, null, 'https://example1.com', null, 'https://example2.com' ], $metadata);
        $this->assertCount(4, $metadata);
        $this->assertEquals(
            'https://example1.com',
            $metadata['AssertionConsumerService:0:Location']
        );
        $this->assertEquals(
            'https://example2.com',
            $metadata['AssertionConsumerService:1:Location']
        );
    }

    /**
     * @dataProvider provideMetaDataTestData
     */
    public function test_it_can_merge_data(MetaData $metaData, MetaData $newData, MetaData $expectation)
    {
        $metaData->merge($newData);

        if ($expectation !== null) {
            self::assertEquals($expectation->getEntityId(), $metaData->getEntityId());
            self::assertEquals($expectation->getMetaDataUrl(), $metaData->getMetaDataUrl());
            self::assertEquals($expectation->getAcsLocations(), $metaData->getAcsLocations());
            self::assertEquals($expectation->getNameIdFormat(), $metaData->getNameIdFormat());
            self::assertEquals($expectation->getCertData(), $metaData->getCertData());
            self::assertEquals($expectation->getDescriptionEn(), $metaData->getDescriptionEn());
            self::assertEquals($expectation->getDescriptionNl(), $metaData->getDescriptionNl());
            self::assertEquals($expectation->getNameEn(), $metaData->getNameEn());
            self::assertEquals($expectation->getNameNl(), $metaData->getNameNl());
            // The logo, organization, contacts and coin merge features are tested in their respective test cases
        } else {
            self::assertNull($metaData->getEntityId());
            self::assertNull($metaData->getMetaDataUrl());
            self::assertNull($metaData->getAcsLocations());
            self::assertNull($metaData->getNameIdFormat());
            self::assertNull($metaData->getCertData());
            self::assertNull($metaData->getDescriptionEn());
            self::assertNull($metaData->getDescriptionNl());
            self::assertNull($metaData->getNameEn());
            self::assertNull($metaData->getNameNl());
            self::assertNull($metaData->getOrganization());
            self::assertNull($metaData->getContacts());
            self::assertNull($metaData->getCoin());
            self::assertNull($metaData->getLogo());
        }
    }

    public function provideMetaDataTestData()
    {
        yield [
            $this->metaData('a'),
            $this->metaData('a'),
            $this->metaData('a'),
        ];
        yield [
            $this->metaData('a'),
            $this->metaData('b'),
            $this->metaData('b'),
        ];
        yield [
            $this->metaData('a'),
            $this->metaData('null'),
            $this->metaData('null'),
        ];
    }

    private function metaData(string $mode)
    {
        switch ($mode) {
            case 'a':
                return new MetaData(
                    'https://www.example.org',
                    'https://www.example.org/metadata',
                    ['https://www.example.org/consume-assertion'],
                    'nameIdFormat-transient',
                    'certData',
                    'Description EN',
                    'Description NL',
                    'Name EN',
                    'Name NL',
                    m::mock(ContactList::class)->makePartial(),
                    m::mock(Organization::class)->makePartial(),
                    m::mock(Coin::class)->makePartial(),
                    m::mock(Logo::class)->makePartial()
                );
            case 'b':
                return new MetaData(
                    'https://www.example.orgB',
                    'https://www.example.org/metadataB',
                    ['https://www.example.org/consume-assertionB'],
                    'nameIdFormat-transientB',
                    'certDataB',
                    'Description B EN',
                    'Description B NL',
                    'Name B EN',
                    'Name B NL',
                    m::mock(ContactList::class)->makePartial(),
                    m::mock(Organization::class)->makePartial(),
                    m::mock(Coin::class)->makePartial(),
                    m::mock(Logo::class)->makePartial()
                );
            case 'null':
                return new MetaData(
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    null,
                    m::mock(ContactList::class)->makePartial(),
                    m::mock(Organization::class)->makePartial(),
                    m::mock(Coin::class)->makePartial(),
                    m::mock(Logo::class)->makePartial()
                );
        }
    }
}
