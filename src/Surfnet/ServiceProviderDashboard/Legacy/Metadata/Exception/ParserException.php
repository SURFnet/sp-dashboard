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

namespace Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception;

use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;

/**
 * ParserException
 */
class ParserException extends InvalidArgumentException
{
    /**
     * @var \LibXMLError[]
     */
    private array $parserErrors = [];

    /**
     * @param \LibXMLError[] $errors
     */
    public function setParserErrors(array $errors): void
    {
        $this->parserErrors = $errors;
    }

    /**
     * @return \LibXMLError[]
     */
    public function getParserErrors(): array
    {
        return $this->parserErrors;
    }
}
