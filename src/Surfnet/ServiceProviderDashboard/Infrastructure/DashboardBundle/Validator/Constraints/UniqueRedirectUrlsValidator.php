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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueRedirectUrlsValidator extends ConstraintValidator
{
    /**
     * @param  string     $value
     * @throws Exception
     */
    public function validate($value, Constraint $constraint): void
    {
        /**
         * @var SaveOidcngEntityCommand $entityCommand
         */
        $entityCommand = $this->context->getRoot()->getData();

        if (!$entityCommand instanceof SaveOidcngEntityCommand) {
            throw new Exception('invalid validator command exception');
        }

        if (is_null($value)) {
            return;
        }

        if (array_unique($value) !== $value) {
            $this->context->addViolation('validator.unique_redirect_urls.duplicate_found');

            return;
        }
    }
}
