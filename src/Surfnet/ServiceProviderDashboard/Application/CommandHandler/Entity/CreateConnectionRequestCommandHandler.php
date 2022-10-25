<?php

declare(strict_types=1);

/**
 * Copyright 2022 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity;

use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CreateConnectionRequestCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;

class CreateConnectionRequestCommandHandler implements CommandHandler
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(CreateConnectionRequestCommand $command)
    {
//        foreach ($command->getConnectionRequests() ?? [] as $connectionRequest) {
//            $institution = $connectionRequest->institution;
//            $name = $connectionRequest->name;
//            $email = $connectionRequest->email;

            // TODO: create Jira tickets...

//            $this->ticketService->createJiraTicket(
//                $entity,
//                $command,
//                $this->issueType,
//                $this->summaryTranslationKey,
//                $this->descriptionTranslationKey
//            );
//        }
    }
}
