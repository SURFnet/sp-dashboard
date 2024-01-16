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
use Exception;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidDateTimeException;
use Webmozart\Assert\Assert;
use function array_key_exists;

class ChangeRequestDto
{
    private function __construct(private readonly string $note, private readonly DateTime $created, private readonly array $pathUpdates)
    {
    }

    /**
     * @throws InvalidDateTimeException
     */
    public static function fromChangeRequest(array $changeRequest): ?ChangeRequestDto
    {
        Assert::isArray($changeRequest);
        Assert::keyExists($changeRequest, 'created', 'No create datetime specified');
        Assert::keyExists($changeRequest, 'pathUpdates', 'No pathUpdates specified');
        Assert::isNonEmptyMap($changeRequest['pathUpdates'], 'No pathUpdates available');

        try {
            $created = new DateTime($changeRequest['created']);
        } catch (Exception) {
            throw new InvalidDateTimeException();
        }
        $note = $changeRequest['note'] ?? '';

        self::flattenArp($changeRequest);

        return new self($note, $created, $changeRequest['pathUpdates']);
    }

    private static function flattenArp(array &$changeRequest): void
    {
        if (array_key_exists('arp', $changeRequest['pathUpdates'])) {
            $arp = $changeRequest['pathUpdates']['arp'];
            if (array_key_exists('attributes', $arp) && !empty($arp['attributes'])) {
                foreach ($arp['attributes'] as $urn => $attribute) {
                    // Include ARP entries in the pathupdates, not in pathUpdates/arp
                    $changeRequest['pathUpdates'][$urn] = $attribute[0]['motivation'];
                }
            }
            unset($changeRequest['pathUpdates']['arp']);
        }
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
