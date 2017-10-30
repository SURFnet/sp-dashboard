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

namespace Surfnet\ServiceProviderDashboard\Application\Factory;

use DateTime;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\AttributesMetadataRepository;

/**
 * Reads the PrivacyQuestions from the Entity that is injected, It than references the answers found in the
 * privacy questions against the privacy question attributes that are found in the AttributesMetadataRepository.
 *
 * The two are merged into an associative array that is comprised of attributename and answer to the privacy question.
 *
 * Example (in json format for readability):
 *
 * {
 *	"coin:privacy:what_data": "All sorts of data will be accessed.",
 *	"coin:privacy:certification": false,
 *	"coin:privacy:certification_valid_from": "2018-06-04",
 *	"coin:privacy:certification_valid_to": "2018-06-06",
 *	"coin:privacy:sn_dpa_why_not": "We can not comply."
 * }
 *
 */
class PrivacyQuestionsMetadataFactory implements MetadataFactory
{
    /**
     * @var AttributesMetadataRepository
     */
    private $repository;

    /**
     * @var Entity
     */
    private $entity;

    public function __construct(AttributesMetadataRepository $repository, Entity $entity)
    {
        $this->entity = $entity;
        $this->repository = $repository;
    }

    public function build()
    {
        $privacyQuestionAnswers = $this->entity->getService()->getPrivacyQuestions();
        $privacyQuestions = $this->repository->findAllPrivacyQuestionsAttributes();

        $attributes = [];

        if ($this->entity->getService()->isPrivacyQuestionsEnabled()) {
            foreach ($privacyQuestions as $question) {
                // Get the associated getter
                $getterName = $question->getterName;
                if (method_exists($privacyQuestionAnswers, $getterName)) {
                    $answer = $privacyQuestionAnswers->$getterName();
                    if (!is_null($answer)) {
                        // Format DateTime to timestamp as manage requires a numeric representation of the date.
                        if ($answer instanceof DateTime) {
                            $answer = (string) $answer->getTimestamp();
                        }
                        // Manage expects booleans as strings.
                        if (is_bool($answer)) {
                            $answer = ($answer) ? '1' : '0';
                        }

                        $attributes[self::METADATA_PREFIX . $question->urns[0]] = $answer;
                    }
                }
            }
        }

        return $attributes;
    }
}
