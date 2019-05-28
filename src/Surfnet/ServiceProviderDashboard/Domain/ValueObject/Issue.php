<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\ValueObject;

use InvalidArgumentException;

class Issue
{
    /**
     * @var string
     */
    private $key;
    /**
     * @var string
     */
    private $issueType;

    /**
     * @param string $key
     * @param string $issueType
     */
    public function __construct($key, $issueType)
    {
        if (!is_string($key) || empty($key)) {
            throw new InvalidArgumentException("An invalid issue key is provided, must be a non empty string");
        }

        if (!is_string($issueType) || empty($issueType)) {
            throw new InvalidArgumentException("An invalid issue type is provided, must be a non empty string");
        }

        $this->key = $key;
        $this->issueType = $issueType;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getIssueType()
    {
        return $this->issueType;
    }
}
