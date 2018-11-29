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

namespace Surfnet\ServiceProviderDashboard\Domain\ValueObject;

/**
 * See https://bugs.php.net/bug.php?id=66773
 */
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact as Applicant;

class Ticket
{
    /** @var string */
    private $assignee = 'conext-beheer';
    /** @var string */
    private $description;
    /** @var string */
    private $entityId;
    /** @var string */
    private $issueType = 'spd-delete-production-entity';
    /** @var string */
    private $priority = 'Medium';
    /** @var string */
    private $reporter = 'sp-dashboard';
    /** @var string */
    private $summary;

    public function __construct($summary, $description, $entityId)
    {
        $this->summary = $summary;
        $this->description = $description;
        $this->entityId = $entityId;
    }

    public static function fromManageResponse($entity, Applicant $applicant)
    {
        $entityId = $entity['data']['entityid'];
        $nameEn = $entity['data']['metaDataFields']['name:en'];

        $summary = sprintf('Request to remove %s from production', $nameEn);
        $description = sprintf(
            'h2. Details
            
            *Applicant name*: %s
            *Applicant email*: %s.',
            $applicant->getDisplayName(),
            $applicant->getEmailAddress()
        );

        return new self($summary, $description, $entityId);
    }

    /**
     * @return string
     */
    public function getAssignee()
    {
        return $this->assignee;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getIssueType()
    {
        return $this->issueType;
    }

    /**
     * @return string
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * @return string
     */
    public function getReporter()
    {
        return $this->reporter;
    }

    /**
     * @return mixed
     */
    public function getSummary()
    {
        return $this->summary;
    }
}
