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
    private array $connectionRequests = [];

    public function __construct(private readonly ManageEntity $manageEntity, private readonly Contact $applicant)
    {
    }

    /**
     * @return ConnectionRequest[]
     */
    public function getConnectionRequests(): array
    {
        return $this->connectionRequests;
    }

    /**
     * @throws ConnectionRequestNotUniqueException
     */
    public function setConnectionRequests(array $connectionRequests): void
    {
        if (count($connectionRequests) !== count($this->getUniqueConnectionRequests($connectionRequests))) {
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

    private function getUniqueConnectionRequests(array $connectionRequests): array
    {
        $result = [];
        foreach ($connectionRequests as $key => $connectionRequest) {
            if (!in_array($connectionRequest->institution, $result, true)) {
                $result[$key] = $connectionRequests;
            }
        }
        return $result;
    }
}
