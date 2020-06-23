<?php

/**
 * Copyright 2020 SURFnet B.V.
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
use Symfony\Component\Validator\Constraints\UrlValidator;

class ValidRedirectUrlValidator extends UrlValidator
{
    /**
     * @param string $value
     * @param Constraint $constraint
     * @throws Exception
     */
    public function validate($value, Constraint $constraint)
    {
        /**
         * @var SaveOidcngEntityCommand $entityCommand
         */
        $entityCommand = $this->context->getRoot()->getData();

        if (!$entityCommand instanceof SaveOidcngEntityCommand) {
            throw new Exception('invalid validator command exception');
        }

        // First validate the regular Url validation
        parent::validate($value, $constraint);

        // Test if we have Url violations, if so re validate the url with the reverse redirect URL rules
        $violations = $this->context->getViolations();
        if ($violations->count() > 0) {
            $parts = parse_url($value);
            if (!isset($parts['host'])) {
                return;
            }

            $clientId = $entityCommand->getClientId();
            $violations->remove(0);
            $storedHost = $parts['host'];
            $parts['host'] = $this->reverseHostname($parts['scheme']);
            if (!substr_count($clientId, $parts['host']) > 0) {
                $this->context->addViolation('validator.redirect_url.reverse_does_not_contain_client_id');
            }
            $parts['scheme'] = $storedHost;
            $newUrl = $this->buildUrl($parts);

            // custom protocol/schemes are allowed for reverse redirect urls
            $constraint->protocols[] = $parts['scheme'];
            parent::validate($newUrl, $constraint);
        }

        if (is_null($value)) {
            return;
        }
    }

    /**
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @param array $parts
     * @return string
     */
    private function buildUrl(array $parts)
    {
        return (isset($parts['scheme']) ? "{$parts['scheme']}:" : '') .
            ((isset($parts['user']) || isset($parts['host'])) ? '//' : '') .
            (isset($parts['user']) ? "{$parts['user']}" : '') .
            (isset($parts['pass']) ? ":{$parts['pass']}" : '') .
            (isset($parts['user']) ? '@' : '') .
            (isset($parts['host']) ? "{$parts['host']}" : '') .
            (isset($parts['port']) ? ":{$parts['port']}" : '') .
            (isset($parts['path']) ? "{$parts['path']}" : '') .
            (isset($parts['query']) ? "?{$parts['query']}" : '') .
            (isset($parts['fragment']) ? "#{$parts['fragment']}" : '');
    }

    private function reverseHostname($hostname)
    {
        return implode('.', array_reverse(explode('.', $hostname)));
    }
}
