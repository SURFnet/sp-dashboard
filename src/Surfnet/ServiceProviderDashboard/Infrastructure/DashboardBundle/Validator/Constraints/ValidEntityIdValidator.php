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
use Pdp\Parser;
use Pdp\PublicSuffixListManager;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidEntityIdValidator extends ConstraintValidator
{
    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            $this->context->addViolation('validator.entity_id.empty');
            return;
        }

        if (!$this->validateUri($value) && !$this->validateUrn($value)) {
            $this->context->addViolation('validator.entity_id.invalid_entity_id');
            return;
        }
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateUrn($value)
    {
        $regex = "/^urn:[a-z0-9][a-z0-9-]{0,31}:[a-z0-9()+,\-.:=@;\$_!*'%\/?#]+$/i";
        $match = preg_match($regex, $value);
        return (bool)$match;
    }

    /**
     * @param $value
     * @return bool
     */
    private function validateUri($value)
    {
        try {
            $pslManager = new PublicSuffixListManager();
            $parser = new Parser($pslManager->getList());
            $parser->parseUrl($value);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }
}
