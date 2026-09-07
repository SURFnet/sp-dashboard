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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use Exception;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\HostBlocklistCheckerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidMetadataUrlValidator extends ConstraintValidator
{
    final public const STATUS_PRIVATE_HOST = 'validator.entity_id.private_host';

    public function __construct(
        private readonly HostBlocklistCheckerInterface $hostBlocklistChecker,
        private readonly bool $allowMetadataPrivateHosts = false,
    ) {
    }

    /**
     * @param string     $value
     */
    public function validate($value, Constraint $constraint): void
    {
        if (empty($value)) {
            return;
        }

        $parser = new UrlParser($value);

        try {
            $parser->parse();
        } catch (Exception) {
            $this->context->addViolation('validator.entity_id.invalid_url');
            return;
        }

        if (!$this->allowMetadataPrivateHosts && $this->hostBlocklistChecker->isBlocked($value)) {
            $this->context->addViolation(self::STATUS_PRIVATE_HOST);
        }
    }
}
