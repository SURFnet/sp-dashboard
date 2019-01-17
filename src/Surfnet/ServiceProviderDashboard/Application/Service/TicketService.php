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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;

class TicketService implements TicketServiceInterface
{
    /**
     * @var TicketServiceInterface
     */
    private $issueRepository;

    /**
     * @param TicketServiceInterface $repository
     */
    public function __construct(TicketServiceInterface $repository)
    {
        $this->issueRepository = $repository;
    }

    public function createIssueFrom(Ticket $ticket)
    {
        return $this->issueRepository->createIssueFrom($ticket);
    }

    public function findByManageIds(array $manageIds)
    {
        return $this->issueRepository->findByManageIds($manageIds);
    }

    public function findByManageId($id)
    {
        return $this->issueRepository->findByManageId($id);
    }

    /**
     * @param string $issueKey
     */
    public function delete($issueKey)
    {
        $this->issueRepository->delete($issueKey);
    }
}
