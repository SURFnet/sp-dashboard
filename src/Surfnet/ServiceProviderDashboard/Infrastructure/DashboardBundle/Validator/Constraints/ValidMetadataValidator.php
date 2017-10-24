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

use Surfnet\ServiceProviderDashboard\Application\Metadata\FetcherInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class ValidMetadataValidator extends ConstraintValidator
{
    /**
     * @var FetcherInterface
     */
    private $fetcher;

    /**
     * @var ParserInterface
     */
    private $parser;

    public function __construct(FetcherInterface $fetcher, ParserInterface $parser)
    {
        $this->fetcher = $fetcher;
        $this->parser = $parser;
    }

    /**
     * @param string     $value
     * @param Constraint $constraint
     */
    public function validate($value, Constraint $constraint)
    {
        if (empty($value)) {
            return;
        }

        try {
            $xml = $this->fetcher->fetch($value);
            $this->parser->parseXml($xml);
        } catch (ParserException $e) {
            $this->context->addViolation($constraint->parseMessage, $this->processErrors($e->getParserErrors()));
        } catch (\Exception $e) {
            $this->context->addViolation($e->getMessage());

            return;
        }
    }

    /**
     * @param \LibXMLError[] $errors
     *
     * @return array
     */
    private function processErrors(array $errors)
    {
        $errorString = PHP_EOL;

        foreach ($errors as $error) {
            $errorString .= 'At line ' . $error->line . ', column ' . $error->column . ': ';
            switch ($error->level) {
                case LIBXML_ERR_WARNING:
                    $errorString .= "Warning $error->code, ";
                    break;
                case LIBXML_ERR_ERROR:
                    $errorString .= "Error $error->code, ";
                    break;
                case LIBXML_ERR_FATAL:
                    $errorString .= "Fatal Error $error->code, ";
                    break;
            }

            $errorString .= trim($error->message) . PHP_EOL;
        }

        return array(
            '%errors%' => $errorString
        );
    }
}
