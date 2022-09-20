<?php

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\Dto;

use DateTime;

class ChangeRequestDto
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $note;

    /**
     * @var datetime
     */
    private $created;

    private $pathUpdates = [];

    private function __construct(string $id, ?string $note, DateTime $created, array $pathUpdates)
    {
        $this->id = $id;
        $this->note = $note;
        $this->created = $created;
        $this->pathUpdates = $pathUpdates;
    }

    public static function fromChangeRequest(array $changeRequest): ?ChangeRequestDto
    {
        $created = new DateTime($changeRequest['created']);

        return new self($changeRequest['id'],
            $changeRequest['note'],
            $created,
            $changeRequest['pathUpdates']);
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getNote(): ?string
    {
        return $this->note;
    }

    public function getCreated(): DateTime
    {
        return $this->created;
    }

    public function getPathUpdates(): array
    {
        return $this->pathUpdates;
    }
}
