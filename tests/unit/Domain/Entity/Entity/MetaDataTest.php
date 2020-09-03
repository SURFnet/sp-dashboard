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
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Coin;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\ContactList;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Logo;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\MetaData;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Organization;

class MetaDataTest extends TestCase
{
    /**
     * @dataProvider provideMetaDataTestData
     */
    public function test_it_can_merge_data(MetaData $metaData, ?MetaData $newData, ?MetaData $expectation)
    {
        $metaData->merge($newData);

        if ($expectation !== null) {
            self::assertEquals($expectation->getEntityId(), $metaData->getEntityId());
            self::assertEquals($expectation->getMetaDataUrl(), $metaData->getMetaDataUrl());
            self::assertEquals($expectation->getAcsLocation(), $metaData->getAcsLocation());
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
            self::assertNull($metaData->getAcsLocation());
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
            null,
            $this->metaData('null'),
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
                    'https://www.example.org/consume-assertion',
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
                    'https://www.example.org/consume-assertionB',
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
