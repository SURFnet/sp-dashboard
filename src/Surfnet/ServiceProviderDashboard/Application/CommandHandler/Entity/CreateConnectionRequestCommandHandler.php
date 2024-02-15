<?php

declare(strict_types = 1);

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

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\CreateConnectionRequestCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Service\TicketService;

class CreateConnectionRequestCommandHandler implements CommandHandler
{
    private string $summaryTranslationKey = 'entity.connection_request.ticket.summary';

    private string $summaryTranslationDescription = 'entity.connection_request.ticket.description';

    public function __construct(private readonly TicketService $ticketService, private readonly string $issueType)
    {
    }

    public function handle(CreateConnectionRequestCommand $command): void
    {
        $this->ticketService->createJiraTicketForConnectionRequests(
            $command,
            $this->issueType,
            $this->summaryTranslationKey,
            $this->summaryTranslationDescription
        );
    }
}
