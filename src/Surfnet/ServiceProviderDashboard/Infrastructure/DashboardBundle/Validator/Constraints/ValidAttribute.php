<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;
use Attribute;

#[Attribute]
class ValidAttribute extends Constraint
{
    #[HasNamedArguments]
    public function __construct(
        public string $messageAttributeNotFound = 'validator.attribute.not_found',
        public string $messageAttributeMotivationNotSet = 'validator.attribute.motivation_not_set',
        public array|string $type = [Constants::TYPE_SAML, Constants::TYPE_OPENID_CONNECT_TNG],
        array $groups = null,
        mixed $payload = null,
    ) {
        parent::__construct([], $groups, $payload);
    }

    public function validatedBy(): string
    {
        return 'valid_attribute';
    }

    public function getDefaultOption(): ?string
    {
        return 'type';
    }
}
