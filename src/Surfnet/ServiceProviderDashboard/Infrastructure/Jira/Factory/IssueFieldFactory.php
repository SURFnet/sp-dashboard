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
use Symfony\Component\Translation\TranslatorInterface;
use Webmozart\Assert\Assert;

class IssueFieldFactory
{
    /**
     * @var string
     */
    private $entityIdFieldName;

    /**
     * @var string
     */
    private $manageIdFieldName;

    /**
     * @var string
     */
    private $reporterFieldName;

    /**
     * @var string
     */
    private $priority;

    /**
     * @var string
     */
    private $projectKey;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    public function __construct(
        string $entityIdFieldName,
        string $manageIdFieldName,
        string $reporterFieldName,
        string $priority,
        string $projectKey,
        TranslatorInterface $translator
    ) {
        Assert::stringNotEmpty(
            $entityIdFieldName,
            'The entity id field name may not be empty, configure in parameters.yml'
        );
        Assert::stringNotEmpty(
            $manageIdFieldName,
            'The manage id field name may not be empty, configure in parameters.yml'
        );
        Assert::stringNotEmpty($priority, 'The priority may not be empty, configure in parameters.yml');
        Assert::stringNotEmpty($projectKey, 'The project key may not be empty, configure in parameters.yml');

        $this->entityIdFieldName = $entityIdFieldName;
        $this->manageIdFieldName = $manageIdFieldName;
        $this->reporterFieldName = $reporterFieldName;
        $this->priority = $priority;
        $this->projectKey = $projectKey;
        $this->translator = $translator;
    }

    public function fromTicket(Ticket $ticket): IssueField
    {
        $issueField = new IssueField();
        $issueField->setProjectKey($this->projectKey)
            ->setDescription($this->translateDescription($ticket))
            ->setSummary($this->translateSummary($ticket))
            ->setIssueType($ticket->getIssueType())
            ->setPriorityName($this->priority)
            ->addCustomField($this->reporterFieldName, sprintf('%s, (%s)', $ticket->getApplicantName(), $ticket->getApplicantEmail()))
            ->addCustomField($this->entityIdFieldName, $ticket->getEntityId())
            ->addCustomField($this->manageIdFieldName, $ticket->getManageId())
        ;

        return $issueField;
    }

    private function translateDescription(Ticket $ticket): string
    {
        return $this->translator->trans($ticket->getDescriptionTranslationKey(), [
            '%applicant_name%' => $ticket->getApplicantName(),
            '%applicant_email%' =>  $ticket->getApplicantEmail(),
            '%entity_name%' => $ticket->getEntityName()
        ]);
    }

    private function translateSummary(Ticket $ticket): string
    {
        return $this->translator->trans($ticket->getSummaryTranslationKey(), [
            '%entity_name%' => $ticket->getEntityName()
        ]);
    }
}
