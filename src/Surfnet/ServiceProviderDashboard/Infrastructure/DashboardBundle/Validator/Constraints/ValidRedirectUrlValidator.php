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
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidRedirectUrlValidator extends UrlValidator
{
    /**
     * @param string $value
     * @param Constraint $constraint
     * @throws Exception
     */
    public function validate($value, Constraint $constraint)
    {
        // This validator is used on a collection of Redirect URLs, Its possible violations are already present.
        $numberOfViolations = $this->context->getViolations()->count();
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
        if ($violations->count() > $numberOfViolations) {
            $parts = parse_url($this->allowSingleSlash($value));
            if (!isset($parts['host'])) {
                return;
            }

            $clientId = $entityCommand->getClientId();
            // Remove the violations that where added just now, we'll run the validator again with the reverse hostname.
            $this->dropLastAddedErrors($violations, $numberOfViolations);

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

    private function allowSingleSlash(string $url)
    {
        $hasDoubleSlash = strpos($url, '://');
        $hasSingleSlash = strpos($url, ':/');
        if (!$hasDoubleSlash && $hasSingleSlash) {
            return str_replace(':/', '://', $url);
        }

        return $url;
    }

    private function dropLastAddedErrors(
        ConstraintViolationListInterface $violations,
        int $numberOfViolationsBeforeExecution
    ) {
        // Convert the violations to an array for easier manipulation
        $violationsArray = $violations->getIterator()->getArrayCopy();
        $numberOfViolations = $violations->count();
        // Now empty the current list of violations
        foreach ($violations as $index => $violation) {
            $violations->offsetUnset($index);
        }

        // Remove the last errors from the array copy
        for ($i=1; $i<=($numberOfViolations - $numberOfViolationsBeforeExecution); $i++) {
            array_pop($violationsArray);
        }

        // Set the array copy values as the list of violations
        foreach ($violationsArray as $index => $violation) {
            $violations->set($index, $violation);
        }
    }
}
