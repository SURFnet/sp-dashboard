<?php

/**
 * Copyright 2017 SURFnet B.V.
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

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoInvalidTypeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoNotFoundException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\HostBlocklistCheckerInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\LogoValidationHelperInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidLogoValidator extends ConstraintValidator
{
    /**
     * The status code used when the download of the logo failed.
     */
    final public const STATUS_DOWNLOAD_FAILED = 'validator.logo.download_failed';

    /**
     * The status code used when an invalid type is requested as logo
     */
    final public const STATUS_INVALID_TYPE = 'validator.logo.wrong_type';

    /**
     * The status code used when the logo URL resolves to a private or reserved address.
     */
    final public const STATUS_PRIVATE_HOST = 'validator.logo.private_host';

    public function __construct(
        private readonly LogoValidationHelperInterface $logoValidationHelper,
        private readonly HostBlocklistCheckerInterface $hostBlocklistChecker,
        private readonly bool $allowLogoPrivateHosts = false,
    ) {
    }

    public function validate($value, Constraint $constraint): void
    {
        if (empty($value)) {
            return;
        }

        $host = parse_url($value, PHP_URL_HOST);
        $ip = $host !== null && $host !== false ? $this->hostBlocklistChecker->resolve($host) : null;

        if (!$this->allowLogoPrivateHosts && $this->hostBlocklistChecker->isIpBlocked($ip)) {
            $this->context->addViolation(self::STATUS_PRIVATE_HOST);
            return;
        }

        try {
            $body = $this->logoValidationHelper->validateLogo($value, $ip);
            if (getimagesizefromstring($body) === false) {
                $this->context->addViolation($constraint->message);
            }
        } catch (LogoNotFoundException) {
            $this->context->addViolation(self::STATUS_DOWNLOAD_FAILED);
        } catch (LogoInvalidTypeException) {
            $this->context->addViolation(self::STATUS_INVALID_TYPE);
        }
    }
}
