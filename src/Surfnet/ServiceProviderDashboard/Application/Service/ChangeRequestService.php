<?php

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

use Surfnet\ServiceProviderDashboard\Application\Dto\ChangeRequestDtoCollection;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Protocol;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityChangeRequestRepository;

class ChangeRequestService implements ChangeRequestServiceInterface
{
    /**
     * @var EntityChangeRequestRepository
     */
    private $repository;

    public function __construct(
        EntityChangeRequestRepository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @throws \Exception
     */
    public function findByIdAndProtocol(string $id, Protocol $protocol): ChangeRequestDtoCollection
    {
        $values = $this->repository->getChangeRequest($id, $protocol);
        return new ChangeRequestDtoCollection($values);
    }
}
