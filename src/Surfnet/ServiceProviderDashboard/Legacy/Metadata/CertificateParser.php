<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Legacy\Metadata;

use Surfnet\ServiceProviderDashboard\Application\Metadata\CertificateParserInterface;

/**
 * Class CertificateParser
 */
class CertificateParser implements CertificateParserInterface
{
    /**
     * @param string $certificateString
     */
    public function parse($certificateString): string
    {
        $certificateString = str_replace('-----BEGIN CERTIFICATE-----', '', $certificateString);
        $certificateString = str_replace('-----END CERTIFICATE-----', '', $certificateString);
        $certificateString = str_replace(["\n", "\r", " ", "\t"], '', $certificateString);
        $certificateString = chunk_split($certificateString, 64, PHP_EOL);

        return "-----BEGIN CERTIFICATE-----" . PHP_EOL . $certificateString . "-----END CERTIFICATE-----";
    }

    /**
     * @param string $certificate
     *
     * @return string
     */
    public function getSubject($certificate)
    {
        $certificateInfo = openssl_x509_parse($certificate);

        return $certificateInfo['name'];
    }
}
