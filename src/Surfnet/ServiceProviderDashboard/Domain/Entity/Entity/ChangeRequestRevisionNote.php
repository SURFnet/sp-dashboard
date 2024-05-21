<?php

declare(strict_types = 1);

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use DateTimeImmutable;
use Stringable;
use const PHP_EOL;

class ChangeRequestRevisionNote implements Stringable
{
    private static string $template =
        'Change request by user %s with email address "%s" via the SPdashboard on %s (%s) ' . PHP_EOL .
        'Comment from user: "%s"';
    public function __construct(
        private ?string $comments,
        private string $commonName,
        private string $emailAddress,
        private JiraTicketNumber $ticketNumber,
    ) {
    }

    public function __toString(): string
    {
        $dateTime = new DateTimeImmutable();
        if ($this->comments === null) {
            $this->comments = 'No comment provided';
        }
        return sprintf(
            self::$template,
            $this->commonName,
            $this->emailAddress,
            $dateTime->format(DateTimeImmutable::ATOM),
            (string) $this->ticketNumber,
            $this->comments
        );
    }
}
