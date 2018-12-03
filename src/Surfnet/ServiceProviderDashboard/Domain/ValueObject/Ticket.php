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
    private $entityId;
    /** @var string */
    private $entityName;
    /** @var string */
    private $applicantName;
    /** @var string */
    private $applicantEmail;

    public function __construct($entityId, $nameEn, $applicantName, $applicantEmail)
    {
        $this->entityId = $entityId;
        $this->entityName = $nameEn;
        $this->applicantName = $applicantName;
        $this->applicantEmail = $applicantEmail;
    }

    public static function fromManageResponse($entity, Applicant $applicant)
    {
        $entityId = $entity['data']['entityid'];
        $nameEn = $entity['data']['metaDataFields']['name:en'];

        return new self($entityId, $nameEn, $applicant->getDisplayName(), $applicant->getEmailAddress());
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    public function getEntityName()
    {
        return $this->entityName;
    }

    public function getApplicantName()
    {
        return $this->applicantName;
    }

    public function getApplicantEmail()
    {
        return $this->applicantEmail;
    }
}
