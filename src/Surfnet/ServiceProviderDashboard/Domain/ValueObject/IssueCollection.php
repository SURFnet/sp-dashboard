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

use Countable;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Issue;

class IssueCollection implements Countable
{
    /**
     * @var Issue[]
     */
    private array $issues = [];

    /**
     * @param Issue[] $issues
     */
    public function __construct(array $issues)
    {
        foreach ($issues as $id => $issue) {
            $this->issues[$id] = $issue;
        }
    }

    public function getIssueById($id): ?Issue
    {
        if (array_key_exists($id, $this->issues)) {
            return $this->issues[$id];
        }

        return null;
    }

    /**
     * Count elements of an object
     *
     * @link   https://php.net/manual/en/countable.count.php
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since  5.1.0
     */
    public function count(): int
    {
        return count($this->issues);
    }
}
