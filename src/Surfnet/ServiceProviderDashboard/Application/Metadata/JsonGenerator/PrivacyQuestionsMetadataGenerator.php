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

namespace Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator;

use stdClass;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;

/**
 * Reads the PrivacyQuestions from the Entity that is injected,
 * it than references the answers found in the privacy questions
 * against the privacy question attributes that are found in the
 * AttributesMetadataRepository.
 *
 * The two are merged into an associative array that is composed
 * of attribute name and answer to the privacy question.
 *
 * Example (in json format for readability):
 *
 * {
 *   "what_data": "All sorts of data will be accessed.",
 *   "security_measures": "We've taken every precaution."
 * }
 *
 */
class PrivacyQuestionsMetadataGenerator implements MetadataGenerator
{
    /**
     * @var true
     */
    private bool $addMetaDataPrefix = false;

    public function __construct(private readonly AttributesMetadataRepository $repository)
    {
    }

    public function build(ManageEntity $entity): array
    {
        $privacyQuestionAnswers = $entity->getService()->getPrivacyQuestions();
        $privacyQuestions = $this->repository->findAllPrivacyQuestionsAttributes();

        $attributes = [];

        if ($privacyQuestionAnswers !== null && $entity->getService()->isPrivacyQuestionsEnabled()) {
            foreach ($privacyQuestions as $question) {
                if ($question->id === 'privacyStatementUrl') {
                    $privacyStatements = $privacyQuestionAnswers->privacyStatementUrls();
                    $privacyStatementsTranslated = [];
                    foreach ($privacyStatements as $urn => $value) {
                        $privacyStatementsTranslated[$this->buildKey($urn)] = $value;
                    }
                    $attributes += $privacyStatementsTranslated;
                    continue;
                }

                $getterName = $question->getterName;
                if (method_exists($privacyQuestionAnswers, $getterName)) {
                    $this->buildPrivacyQuestion(
                        $attributes,
                        $getterName,
                        $privacyQuestionAnswers,
                        $question
                    );
                }
            }
        }

        return $attributes;
    }

    public function withMetadataPrefix(): void
    {
        $this->addMetaDataPrefix = true;
    }

    private function buildKey(string $urn)
    {
        if ($this->addMetaDataPrefix) {
            return 'metaDataFields.' . $urn;
        }
        return $urn;
    }

    public function buildPrivacyQuestion(
        array &$attributes,
        string $getterName,
        PrivacyQuestions $privacyQuestionAnswers,
        mixed $question
    ): void {
        $answer = $privacyQuestionAnswers->$getterName();
        if (!is_null($answer)) {
            // Manage expects booleans as strings.
            if (is_bool($answer)) {
                $answer = ($answer) ? '1' : '0';
            }
            $key = $this->buildKey($question->urns[0]);
            $attributes[$key] = $answer;
        }
    }
}
