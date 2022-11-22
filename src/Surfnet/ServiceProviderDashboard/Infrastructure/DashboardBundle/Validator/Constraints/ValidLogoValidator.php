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

use Exception;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoInvalidTypeException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Exception\LogoNotFoundException;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\LogoValidationHelperInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidLogoValidator extends ConstraintValidator
{
    /**
     * The status code used when the download of the logo failed.
     */
    const STATUS_DOWNLOAD_FAILED = 'validator.logo.download_failed';

    /**
     * The status code used when an invalid type is requested as logo
     */
    const STATUS_INVALID_TYPE = 'validator.logo.wrong_type';

    /**
     * @var LogoValidationHelperInterface
     */
    private $logoValidationHelper;

    public function __construct(LogoValidationHelperInterface $logoValidationHelper)
    {
        $this->logoValidationHelper = $logoValidationHelper;
    }

    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        try {
            $this->logoValidationHelper->validateLogo($value);
            $this->getImageSizeValidation($value, $constraint);
        } catch (LogoNotFoundException $e) {
            $this->context->addViolation(self::STATUS_DOWNLOAD_FAILED);
            return;
        } catch (LogoInvalidTypeException $e) {
            $this->context->addViolation(self::STATUS_INVALID_TYPE);
            return;
        }
    }

    /**
     * Using getimagesize we can test if PHP can handle the resource as an image
     * @param $value
     * @param Constraint $constraint
     */
    private function getImageSizeValidation($value, Constraint $constraint)
    {
        try {
            $imgData = getimagesize($value);
            if ($imgData === false) {
                $this->context->addViolation($constraint->message);
            }
        } catch (Exception $e) {
            $this->context->addViolation($constraint->message);
        }
    }
}
