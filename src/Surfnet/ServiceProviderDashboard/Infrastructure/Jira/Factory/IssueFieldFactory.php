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
    /**
     * @var string
     */
    private $assignee;

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
    private $priority;

    /**
     * @var string
     */
    private $projectKey;

    /**
     * @var string
     */
    private $reporter;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @param string $assignee
     * @param string $entityIdFieldName
     * @param string $manageIdFieldName
     * @param string $priority
     * @param string $projectKey
     * @param string $reporter
     * @param TranslatorInterface $translator
     */
    public function __construct(
        $assignee,
        $entityIdFieldName,
        $manageIdFieldName,
        $priority,
        $projectKey,
        $reporter,
        TranslatorInterface $translator
    ) {
        Assert::stringNotEmpty($assignee, 'The assignee may not be empty, configure in parameters.yml');
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
        Assert::stringNotEmpty($reporter, 'The reporter may not be empty, configure in parameters.yml');

        $this->assignee = $assignee;
        $this->entityIdFieldName = $entityIdFieldName;
        $this->manageIdFieldName = $manageIdFieldName;
        $this->priority = $priority;
        $this->projectKey = $projectKey;
        $this->reporter = $reporter;
        $this->translator = $translator;
    }

    public function fromTicket(Ticket $ticket)
    {
        $issueField = new IssueField();
        $issueField->setProjectKey($this->projectKey)
            ->setDescription($this->translateDescription($ticket))
            ->setSummary($this->translateSummary($ticket))
            ->setIssueType($ticket->getIssueType())
            ->setPriorityName($this->priority)
            ->setAssigneeName($this->assignee)
            ->setReporterName($this->reporter)
            ->addCustomField($this->entityIdFieldName, $ticket->getEntityId())
            ->addCustomField($this->manageIdFieldName, $ticket->getManageId())
        ;

        return $issueField;
    }

    private function translateDescription(Ticket $ticket)
    {
        return $this->translator->trans($ticket->getDescriptionTranslationKey(), [
            '%applicant_name%' => $ticket->getApplicantName(),
            '%applicant_email%' =>  $ticket->getApplicantEmail(),
            '%entity_name%' => $ticket->getEntityName()
        ]);
    }

    private function translateSummary(Ticket $ticket)
    {
        return $this->translator->trans($ticket->getSummaryTranslationKey(), [
            '%entity_name%' => $ticket->getEntityName()
        ]);
    }
}
