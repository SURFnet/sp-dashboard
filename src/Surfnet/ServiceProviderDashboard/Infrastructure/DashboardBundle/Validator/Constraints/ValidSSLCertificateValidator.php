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

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidSSLCertificateValidator extends ConstraintValidator
{

    /**
     * @param mixed      $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (null === $value || '' === $value) {
            return;
        }

        $value = $this->setCertificateEnvelope($value);

        $cert = openssl_x509_parse($value);

        if ($cert === false) {
            $this->context->addViolation($constraint->message);

            return;
        }

        openssl_x509_export($value, $cert, false);

        $matches = [];
        if (!preg_match('~Public-Key: \((\d+) bit\)~', $cert, $matches)) {
            $this->context->addViolation('validator.ssl_certificate.unknown_key_length');

            return;
        }

        if ($matches[1] < 2048) {
            $this->context->addViolation(
                'validator.ssl_certificate.wrong_key_length',
                array(
                    '%length%' => $matches[1]
                )
            );

            return;
        }
    }

    private function setCertificateEnvelope($certData)
    {
        $certData = $this->stripCertificateEnvelope($certData);
        $certData = $this->addCertificateEnvelope($certData);

        return $certData;
    }

    private function stripCertificateEnvelope($certData)
    {
        $certData = str_replace('-----BEGIN CERTIFICATE-----', '', $certData);
        $certData = str_replace('-----END CERTIFICATE-----', '', $certData);

        return trim($certData);
    }

    private function addCertificateEnvelope($certData)
    {
        return "-----BEGIN CERTIFICATE-----" . PHP_EOL . $certData . PHP_EOL . "-----END CERTIFICATE-----";
    }
}
