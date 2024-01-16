<?php

/**
 * Copyright 2023 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use function in_array;

/**
 * Representation of the different DPA types Data Processing Agreement
 * That we currently allow.
 *
 * Note that:
 * - The possible DPA varieties match the Manage coin:privacy:dpa_type enum options
 * - The `service_has_own_dpa` enum is chosen as the `coin:privacy:dpa_type` default value,
 *   as prescribed in the coin json schema definition.
 *
 * Data type definition of the coin:privacy:dpa_type from Manage:
 *  "coin:privacy:dpa_type": {
 *    "type": "string",
 *    "enum": [
 *      "dpa_not_applicable",
 *      "dpa_in_surf_agreement",
 *      "dpa_model_surf",
 *      "dpa_supplied_by_service",
 *      "other"
 *    ],
 *    "default" : "service_has_own_dpa",
 *    "info": "Determines what DPA this service has to offer"
 *  }
 */
class DpaType implements \Stringable
{
    private const DPA_TYPE_NOT_APPLICABLE = 'dpa_not_applicable';
    private const DPA_TYPE_MODEL_SURF = 'dpa_model_surf';
    private const DPA_TYPE_IN_SURF_AGREEMENT = 'dpa_in_surf_agreement';
    private const DPA_TYPE_SUPPLIED_BY_SERVICE = 'dpa_supplied_by_service';
    private const DPA_TYPE_OTHER = 'other';

    final public const DEFAULT = self::DPA_TYPE_SUPPLIED_BY_SERVICE;

    private static array $allowedDpaTypes = [
        'privacy.form.dpaType.choice.dpa-not-applicable' => self::DPA_TYPE_NOT_APPLICABLE,
        'privacy.form.dpaType.choice.through-surf' => self::DPA_TYPE_MODEL_SURF,
        'privacy.form.dpaType.choice.in-surf-agreement' => self::DPA_TYPE_IN_SURF_AGREEMENT,
        'privacy.form.dpaType.choice.dpa-supplied-by-service' => self::DPA_TYPE_SUPPLIED_BY_SERVICE,
        'privacy.form.dpaType.choice.other' => self::DPA_TYPE_OTHER
    ];

    private function __construct(public readonly string $type)
    {
    }

    public static function from(PrivacyQuestions $questions): DpaType
    {
        $type = $questions->getDpaType();
        if (is_null($type)) {
            $type = self::DEFAULT;
        }
        return self::build($type);
    }

    public static function fromString(string $dpaType)
    {
        return self::build($dpaType);
    }

    public static function build(string $type): self
    {
        if (in_array($type, self::$allowedDpaTypes, true)) {
            return new self($type);
        }
        // Not set, or unknown DPA type defaults to the default
        return new self(self::DEFAULT);
    }

    public static function choices(): array
    {
        return self::$allowedDpaTypes;
    }

    public function __toString(): string
    {
        return $this->type;
    }
}
