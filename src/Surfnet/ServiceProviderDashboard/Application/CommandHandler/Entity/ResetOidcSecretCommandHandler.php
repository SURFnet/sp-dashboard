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

use League\Tactician\CommandBus;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\ResetOidcSecretCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Secret;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;

class ResetOidcSecretCommandHandler implements CommandHandler
{
    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    /**
     * @var PublishEntityClient
     */
    private $publishEntityClient;

    public function __construct(
        CommandBus $commandBus,
        AuthorizationService $authorizationService,
        PublishEntityClient $publishEntityClient
    ) {
        $this->commandBus = $commandBus;
        $this->authorizationService = $authorizationService;
        $this->publishEntityClient = $publishEntityClient;
    }

    /**
     * @param ResetOidcSecretCommand $command
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    public function handle(ResetOidcSecretCommand $command)
    {
        $entity = $command->getManageEntity();

        $protocol = $entity->getProtocol()->getProtocol();
        if ($protocol !== Constants::TYPE_OPENID_CONNECT_TNG &&
            $protocol !== Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER
        ) {
            throw new InvalidArgumentException('Only OIDC TNG entities can be processed');
        }

        $status = $entity->getStatus();
        if ($status !== Constants::STATE_PUBLISHED && $status !== Constants::STATE_PUBLICATION_REQUESTED) {
            throw new InvalidArgumentException('The requested entity can not be processed, invalid state');
        }

        $secret = new Secret(Constants::OIDC_SECRET_LENGTH);

        $entity->updateClientSecret($secret);

        if ($entity->getEnvironment() === Constants::ENVIRONMENT_PRODUCTION) {
            $publishCommand = new PublishEntityProductionCommand($entity, $this->authorizationService->getContact());
            $this->commandBus->handle($publishCommand);
            if (!$entity->isExcludedFromPush()) {
                // Push metadata (we push to production manage upon client secret resets)
                // https://www.pivotaltracker.com/story/show/173009970
                $this->publishEntityClient->pushMetadata();
            }
        } else if ($entity->getEnvironment() === Constants::ENVIRONMENT_TEST) {
            $publishCommand = new PublishEntityTestCommand($entity);
            $this->commandBus->handle($publishCommand);
        }
    }
}
