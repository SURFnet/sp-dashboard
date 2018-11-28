<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory;

use JiraRestApi\Issue\IssueField;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;

class IssueFieldFactory
{
    const CUSTOM_FIELD_ENTITY_ID = 'customfield_13018';
    const CUSTOM_FIELD_APPLICANT_NAME = 'customfield_11111';
    const CUSTOM_FIELD_APPLICANT_EMAIL = 'customfield_22222';

    public function fromTicket(Ticket $ticket)
    {
        $issueField = new IssueField();
        $issueField->setProjectKey("CXT")
            ->setDescription($ticket->getDescription())
            ->setIssueType($ticket->getIssueType())
            ->setSummary($ticket->getSummary())
            ->setPriorityName($ticket->getPriority())
            ->setAssigneeName($ticket->getAssignee())
            ->setReporterName($ticket->getReporter())
            ->addCustomField(self::CUSTOM_FIELD_ENTITY_ID, $ticket->getEntityId())
            ->addCustomField(self::CUSTOM_FIELD_APPLICANT_NAME, $ticket->getApplicantName())
            ->addCustomField(self::CUSTOM_FIELD_APPLICANT_EMAIL, $ticket->getApplicantEmail())
        ;

        return $issueField;
    }
}
