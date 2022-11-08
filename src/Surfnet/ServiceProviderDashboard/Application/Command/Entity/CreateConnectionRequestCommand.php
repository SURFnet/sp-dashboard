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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Entity;

use Exception;
use Surfnet\ServiceProviderDashboard\Application\Exception\ConnectionRequestNotUniqueException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\ConnectionRequest;

class CreateConnectionRequestCommand implements CreateConnectionRequestCommandInterface
{
    /**
     * @var ConnectionRequest[]
     */
    private $connectionRequests = [];

    /**
     * @var ManageEntity
     */
    private $manageEntity;

    /**
     * @var Contact
     */
    private $applicant;

    public function __construct(ManageEntity $manageEntity, Contact $applicant)
    {
        $this->manageEntity = $manageEntity;
        $this->applicant = $applicant;
    }

    /**
     * @return ConnectionRequest[]
     */
    public function getConnectionRequests(): array
    {
        return $this->connectionRequests;
    }

    /**
     * @throws Exception
     */
    public function setConnectionRequests(array $connectionRequests): void
    {
        if (count($connectionRequests) !== count(array_unique($connectionRequests))) {
            throw new ConnectionRequestNotUniqueException('Connection request should be unique');
        }
        $this->connectionRequests = $connectionRequests;
    }

    public function getManageEntity(): ManageEntity
    {
        return $this->manageEntity;
    }

    public function getApplicant(): Contact
    {
        return $this->applicant;
    }
}
