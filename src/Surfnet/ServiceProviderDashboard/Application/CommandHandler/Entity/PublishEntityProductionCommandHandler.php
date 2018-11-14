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

namespace Surfnet\ServiceProviderDashboard\Application\CommandHandler\Entity;

use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Mail\PublishToProductionMailCommand;
use Surfnet\ServiceProviderDashboard\Application\CommandHandler\CommandHandler;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Exception\NotAuthenticatedException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\MailMessageFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Mailer\Message;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Security\Identity;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\PublishMetadataException;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class PublishEntityProductionCommandHandler implements CommandHandler
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var PublishEntityRepository
     */
    private $publishClient;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var MailMessageFactory
     */
    private $mailMessageFactory;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var FlashBagInterface
     */
    private $flashBag;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityRepository $entityRepository,
        PublishEntityRepository $publishClient,
        CommandBus $commandBus,
        MailMessageFactory $mailMessageFactory,
        TokenStorageInterface $tokenStorage,
        FlashBagInterface $flashBag,
        LoggerInterface $logger
    ) {
        $this->repository = $entityRepository;
        $this->publishClient = $publishClient;
        $this->commandBus = $commandBus;
        $this->mailMessageFactory = $mailMessageFactory;
        $this->tokenStorage = $tokenStorage;
        $this->flashBag = $flashBag;
        $this->logger = $logger;
    }

    /**
     * Publishes the entity to production
     *
     * Some remarks:
     *  - The production manage connection is used to publish to production
     *  - In addition to a test publish; the coin:exclude_from_push attribute is passed with value 1
     *  - The mail message is still sent to the service desk.
     *
     * @param PublishEntityProductionCommand $command
     * @throws NotAuthenticatedException
     */
    public function handle(PublishEntityProductionCommand $command)
    {
        $entity = $this->repository->findById($command->getId());

        try {
            $this->logger->info(
                sprintf('Publishing entity "%s" to Manage in production environment', $entity->getNameNl())
            );

            $publishResponse = $this->publishClient->publish($entity);

            if (array_key_exists('id', $publishResponse)) {
                // Send the confirmation mail
                $this->logger->info(
                    sprintf('Sending publish request mail to service desk for "%s".', $entity->getNameNl())
                );
                $mailCommand = new PublishToProductionMailCommand(
                    $this->buildMailMessage($entity)
                );
                $this->commandBus->handle($mailCommand);
            }
        } catch (PublishMetadataException $e) {
            $this->logger->error(
                sprintf(
                    'Publishing to Manage failed for: "%s". Message: "%s"',
                    $entity->getNameNl(),
                    $e->getMessage()
                )
            );
            $this->flashBag->add('error', 'entity.edit.error.publish');
        }

        // Set entity status to published
        $entity->setStatus(Entity::STATE_PUBLISHED);
        $this->logger->info(sprintf('Updating status of "%s" to published.', $entity->getNameNl()));

        // Save changes made to entity
        $this->repository->save($entity);
    }

    /**
     * @param Entity $entity
     * @return Message
     * @throws NotAuthenticatedException
     */
    private function buildMailMessage(Entity $entity)
    {
        $token = $this->tokenStorage->getToken();
        if (!$token instanceof TokenInterface) {
            throw new NotAuthenticatedException(
                'No authentication token found'
            );
        }

        $user = $token->getUser();
        if (!$user instanceof Identity) {
            throw new NotAuthenticatedException(
                'No user found in authentication token'
            );
        }

        $contact = $user->getContact();
        if (!$contact instanceof Contact) {
            throw new NotAuthenticatedException(
                'Unable to determine contact information of authenticated user'
            );
        }

        return $this->mailMessageFactory->buildPublishToProductionMessage($entity, $contact);
    }
}
