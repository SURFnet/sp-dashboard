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

namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityConnectionRequestRepository;

class ConnectionRequestService implements ConnectionRequestServiceInterface
{
    /**
     * @var EntityConnectionRequestRepository
     */
    private $repository;

    public function __construct(EntityConnectionRequestRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getOpenConnectionRequests(string $id): array
    {
        return $this->repository->getOpenConnectionRequest($id);
    }
}
