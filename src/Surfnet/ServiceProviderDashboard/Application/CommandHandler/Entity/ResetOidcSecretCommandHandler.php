<?php

/**
 * Copyright 2019 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity;

use Surfnet\ServiceProviderDashboard\Application\Command\Entity\ResetOidcSecretCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\EntityNotFoundException;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\LoadEntityService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Secret;

class ResetOidcSecretCommandHandler implements CommandHandler
{
    /**
     * @var EntityRepository
     */
    private $repository;
    /**
     * @var LoadEntityService
     */
    private $loadEntityService;

    /**
     * @param EntityRepository $repository
     * @param LoadEntityService $loadEntityService
     */
    public function __construct(EntityRepository $repository, LoadEntityService $loadEntityService)
    {
        $this->repository = $repository;
        $this->loadEntityService = $loadEntityService;
    }

    /**
     * @param ResetOidcSecretCommand $command
     * @throws EntityNotFoundException
     * @throws InvalidArgumentException
     * @throws \Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException
     */
    public function handle(ResetOidcSecretCommand $command)
    {
        $entity = $this->loadEntityService->load(
            $command->getId(),
            $command->getManageId(),
            $command->getService(),
            $command->getEnvironment(),
            $command->getEnvironment()
        );

        if (!$entity) {
            throw new EntityNotFoundException('The requested entity could not be found');
        }

        $protocol = $entity->getProtocol();
        if ($protocol !== Constants::TYPE_OPENID_CONNECT_TNG &&
            $protocol !== Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER
        ) {
            throw new EntityNotFoundException('The requested entity could be found, invalid protocol');
        }

        if ($entity->getStatus() !== Constants::STATE_PUBLISHED) {
            throw new EntityNotFoundException('The requested entity could be found, invalid state');
        }

        $secret = new Secret(Constants::OIDC_SECRET_LENGTH);

        $entity->setClientSecret($secret->getSecret());
        $entity->setManageId($command->getManageId());
        $this->repository->save($entity);
    }
}
