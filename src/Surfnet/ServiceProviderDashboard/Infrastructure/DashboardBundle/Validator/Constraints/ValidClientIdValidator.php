<?php

//declare(strict_types = 1);

/**
 * Copyright 2018 SURFnet B.V.
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
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidClientIdValidator extends ConstraintValidator
{
    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint): void
    {
        if (empty($value)) {
            $this->context->addViolation('validator.client_id.empty');
            return;
        }

        $parser = new UrlParser($value);

        try {
            $urlParts = $parser->parse();
            if (isset($urlParts['port'])) {
                $this->context->addViolation('validator.client_id.no_colon');
            }
        } catch (Exception) {
            $this->context->addViolation('validator.client_id.invalid_url');
            return;
        }
    }
}
