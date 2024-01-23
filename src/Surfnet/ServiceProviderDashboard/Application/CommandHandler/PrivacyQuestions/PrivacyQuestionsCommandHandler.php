<?php

//declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\PrivacyQuestions;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\PrivacyQuestions\PrivacyQuestionsCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PrivacyQuestionsRepository;

class PrivacyQuestionsCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly PrivacyQuestionsRepository $repository,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * @throws EntityNotFoundException
     */
    public function handle(PrivacyQuestionsCommand $command): void
    {
        $service = $command->getService();

        switch ($command->getMode()) {
            case PrivacyQuestionsCommand::MODE_EDIT:
                // Fetch an existing entity
                $questions = $this->repository->findByService($service);
                if (is_null($questions)) {
                    $this->logger->error(
                        sprintf('Unable to fetch the privacy questions for "%s"', $service->getName())
                    );
                    throw new EntityNotFoundException("Unable to find privacy question");
                }
                break;
            default:
            case PrivacyQuestionsCommand::MODE_CREATE:
                // Create a new entity
                $questions = new PrivacyQuestions();
                break;
        }

        $this->setDataFromCommand($command, $questions);
        $this->repository->save($questions);
    }

    private function setDataFromCommand(PrivacyQuestionsCommand $command, PrivacyQuestions $questions): void
    {
        $questions->setService($command->getService());
        $questions->setAccessData($command->getAccessData());
        $questions->setCountry($command->getCountry());
        $questions->setOtherInfo($command->getOtherInfo());
        $questions->setSecurityMeasures($command->getSecurityMeasures());
        $questions->setWhatData($command->getWhatData());
        $questions->setDpaType($command->getDpaType()->type);
        if ($command->privacyStatementUrlEn) {
            $questions->setPrivacyStatementUrlEn($command->privacyStatementUrlEn);
        }
        if ($command->privacyStatementUrlNl) {
            $questions->setPrivacyStatementUrlNl($command->privacyStatementUrlNl);
        }
    }
}
