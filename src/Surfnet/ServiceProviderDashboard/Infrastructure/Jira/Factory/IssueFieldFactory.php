<?php

//declare(strict_types = 1);

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
use JiraRestApi\Issue\IssueType;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Ticket;
use Symfony\Contracts\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class IssueFieldFactory
{
    public function __construct(
        private readonly string $entityIdFieldName,
        private readonly string $manageIdFieldName,
        private readonly string $reporterFieldName,
        private readonly string $priority,
        private readonly string $projectKey,
        private readonly TranslatorInterface $translator
    ) {
        Assert::stringNotEmpty(
            $entityIdFieldName,
            'The entity id field name may not be empty, configure in .env'
        );
        Assert::stringNotEmpty(
            $manageIdFieldName,
            'The manage id field name may not be empty, configure in .env'
        );
        Assert::stringNotEmpty($priority, 'The priority may not be empty, configure in .env');
        Assert::stringNotEmpty($projectKey, 'The project key may not be empty, configure in .env');
    }

    public function fromTicket(Ticket $ticket): IssueField
    {
        $issueField = new IssueField();
        $issueField->setProjectKey($this->projectKey)
            ->setDescription($this->translateDescription($ticket))
            ->setSummary($this->translateSummary($ticket))
            ->setIssueTypeAsString($ticket->getIssueType())
            ->setPriorityNameAsString($this->priority)
            ->addCustomField($this->reporterFieldName, $ticket->getApplicantEmail())
            ->addCustomField($this->entityIdFieldName, $ticket->getEntityId())
            ->addCustomField($this->manageIdFieldName, $ticket->getManageId());

        return $issueField;
    }

    public function fromConnectionRequestTicket(Ticket $ticket): IssueField
    {
        $issueField = new IssueField();
        $issueField->setProjectKey($this->projectKey)
            ->setDescription($this->translateConnectionRequestDescriptions($ticket))
            ->setSummary($this->translateSummary($ticket))
            ->setIssueTypeAsString($ticket->getIssueType())
            ->setPriorityNameAsString($this->priority)
            ->addCustomField($this->reporterFieldName, $ticket->getApplicantEmail())
            ->addCustomField($this->entityIdFieldName, $ticket->getEntityId())
            ->addCustomField($this->manageIdFieldName, $ticket->getManageId());

        return $issueField;
    }

    private function translateDescription(Ticket $ticket): string
    {
        return $this->translator->trans(
            $ticket->getDescriptionTranslationKey(),
            [
            '%applicant_name%' => $ticket->getApplicantName(),
            '%applicant_email%' =>  $ticket->getApplicantEmail(),
            '%entity_name%' => $ticket->getEntityName()
            ]
        );
    }

    private function translateConnectionRequestDescriptions(Ticket $ticket): string
    {
        $translation = '';
        $translationKey = 'entity.connection_request.ticket.applicant';
        $translation .= $this->translator->trans(
            $translationKey,
            [
            '%applicant_name%' => $ticket->getApplicantName(),
            '%applicant_email%' =>  $ticket->getApplicantEmail(),
            '%entity_name%' => $ticket->getEntityName()]
        );

        $translationKey = 'entity.connection_request.ticket.institution';
        foreach ($ticket->getConnectionRequests() ?? [] as $connectionRequest) {
            $translation .= $this->translator->trans(
                $translationKey,
                [
                '%institution_name%' => $connectionRequest->institution,
                '%contact_name%' => $connectionRequest->name,
                '%contact_email%' => $connectionRequest->email
                ]
            );
        }
        return $translation;
    }

    private function translateSummary(Ticket $ticket): string
    {
        return $this->translator->trans(
            $ticket->getSummaryTranslationKey(),
            [
            '%entity_name%' => $ticket->getEntityName()
            ]
        );
    }
}
