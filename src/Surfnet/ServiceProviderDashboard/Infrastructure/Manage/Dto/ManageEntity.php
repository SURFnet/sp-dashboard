<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto;

class ManageEntity
{
    private $id;

    /**
     * @var AttributeList
     */
    private $attributes;

    /**
     * @var MetaData
     */
    private $metaData;

    public static function fromApiResponse($data)
    {
        $attributeList = AttributeList::fromApiResponse($data);
        $metaData = MetaData::fromApiResponse($data);
        return new self($data['id'], $attributeList, $metaData);
    }

    /**
     * @param string $id
     * @param AttributeList $attributes
     * @param MetaData $metaData
     */
    private function __construct($id, AttributeList $attributes, MetaData $metaData)
    {
        $this->id = $id;
        $this->attributes = $attributes;
        $this->metaData = $metaData;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getMetaData()
    {
        return $this->metaData;
    }
}
