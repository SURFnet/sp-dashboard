<?php

//declare(strict_types = 1);

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
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\QueryServiceProviderException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\PublishEntityClient;

/**
 * @SuppressWarnings(PHPMD.LongClassName)
 */
class ResetOidcSecretCommandHandler implements CommandHandler
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly AuthorizationService $authorizationService,
        private readonly PublishEntityClient $publishProdEntityClient,
        private readonly PublishEntityClient $publishTestEntityClient
    ) {
    }

    /**
     * @throws InvalidArgumentException
     * @throws QueryServiceProviderException
     */
    public function handle(ResetOidcSecretCommand $command): void
    {
        $entity = $command->getManageEntity();

        $protocol = $entity->getProtocol()->getProtocol();
        if ($protocol !== Constants::TYPE_OPENID_CONNECT_TNG
            && $protocol !== Constants::TYPE_OPENID_CONNECT_TNG_RESOURCE_SERVER
            && $protocol !== Constants::TYPE_OAUTH_CLIENT_CREDENTIAL_CLIENT
        ) {
            throw new InvalidArgumentException('Only OIDC TNG and Oauth CC entities can be processed');
        }

        $secret = new Secret(Constants::OIDC_SECRET_LENGTH);

        $entity->updateClientSecret($secret);

        if ($entity->isProduction()) {
            $publishCommand = new PublishEntityProductionCommand($entity, $this->authorizationService->getContact());
            $publishCommand->markPublishClientReset();
            $this->commandBus->handle($publishCommand);
        } elseif ($entity->getEnvironment() === Constants::ENVIRONMENT_TEST) {
            $publishCommand = new PublishEntityTestCommand($entity);
            $this->commandBus->handle($publishCommand);
        }
        if (!$entity->isExcludedFromPush()) {
            // Push metadata (we push to production manage upon client secret resets)
            // https://www.pivotaltracker.com/story/show/173009970
            if ($entity->isProduction()) {
                $this->publishProdEntityClient->pushMetadata();
                return;
            }
            $this->publishTestEntityClient->pushMetadata();
        }
    }
}
