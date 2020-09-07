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
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;

class Ticket
{
    /** @var string */
    private $entityId;
    /** @var string */
    private $manageId;
    /** @var string */
    private $entityName;
    /** @var string */
    private $summaryTranslationKey;
    /** @var string */
    private $descriptionTranslationKey;
    /** @var string */
    private $applicantName;
    /** @var string */
    private $applicantEmail;
    /** @var string */
    private $issueType;

    public function __construct(
        string $entityId,
        string $manageId,
        string $nameEn,
        string $summaryTranslationKey,
        string $descriptionTranslationKey,
        string $applicantName,
        string $applicantEmail,
        string $issueType
    ) {
        $this->entityId = $entityId;
        $this->manageId = $manageId;
        $this->entityName = $nameEn;
        $this->summaryTranslationKey = $summaryTranslationKey;
        $this->descriptionTranslationKey = $descriptionTranslationKey;
        $this->applicantName = $applicantName;
        $this->applicantEmail = $applicantEmail;
        $this->issueType = $issueType;
    }

    public static function fromManageResponse(
        ManageEntity $entity,
        Applicant $applicant,
        string $issueType,
        string $summaryTranslationKey,
        string $descriptionTranslationKey
    ) : Ticket {
        $entityId = $entity->getMetaData()->getEntityId();
        $nameEn = $entity->getMetaData()->getNameEn();

        return new self(
            $entityId,
            $entity->getId(),
            $nameEn,
            $summaryTranslationKey,
            $descriptionTranslationKey,
            $applicant->getDisplayName(),
            $applicant->getEmailAddress(),
            $issueType
        );
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getManageId(): string
    {
        return $this->manageId;
    }

    public function getEntityName(): string
    {
        return $this->entityName;
    }

    public function getApplicantName(): string
    {
        return $this->applicantName;
    }

    public function getApplicantEmail(): string
    {
        return $this->applicantEmail;
    }

    public function getIssueType(): string
    {
        return $this->issueType;
    }

    public function getSummaryTranslationKey(): string
    {
        return $this->summaryTranslationKey;
    }

    public function getDescriptionTranslationKey(): string
    {
        return $this->descriptionTranslationKey;
    }
}
