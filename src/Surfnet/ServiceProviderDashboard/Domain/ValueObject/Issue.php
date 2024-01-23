<?php

//declare(strict_types = 1);

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
use JsonSerializable;

class Issue implements JsonSerializable
{
    final public const IDENTIFIER_KEY = 'key';
    final public const IDENTIFIER_ISSUE_TYPE = 'issueType';
    final public const IDENTIFIER_TICKET_STATUS = 'ticketStatus';

    final public const STATUS_CLOSED = 'CLOSED';
    final public const STATUS_RESOLVED = 'RESOLVED';
    final public const STATUS_OPEN = 'To Do';

    private readonly string $key;

    private readonly string $issueType;

    public function __construct(string $key, string $issueType, private readonly string $ticketStatus)
    {
        if (!is_string($key) || ($key === '' || $key === '0')) {
            throw new InvalidArgumentException("An invalid issue key is provided, must be a non empty string");
        }

        if (!is_string($issueType) || ($issueType === '' || $issueType === '0')) {
            throw new InvalidArgumentException("An invalid issue type is provided, must be a non empty string");
        }

        $this->key = $key;
        $this->issueType = $issueType;
    }

    public static function fromSerializedData(array $issueData): self
    {
        return new self(
            $issueData[self::IDENTIFIER_KEY],
            $issueData[self::IDENTIFIER_ISSUE_TYPE],
            $issueData[self::IDENTIFIER_TICKET_STATUS]
        );
    }

    /**
     * @return string
     */
    public function getKey(): string
    {
        return $this->key;
    }

    /**
     * @return string
     */
    public function getIssueType(): string
    {
        return $this->issueType;
    }

    public function isClosedOrResolved(): bool
    {
        return $this->ticketStatus === self::STATUS_CLOSED || $this->ticketStatus === self::STATUS_RESOLVED;
    }

    public function jsonSerialize(): mixed
    {
        return [
            self::IDENTIFIER_KEY => $this->key,
            self::IDENTIFIER_ISSUE_TYPE => $this->issueType,
            self::IDENTIFIER_TICKET_STATUS => $this->ticketStatus,
        ];
    }
}
