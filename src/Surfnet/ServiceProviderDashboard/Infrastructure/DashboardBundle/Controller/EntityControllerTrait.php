<?php

//declare(strict_types = 1);

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Controller;

use Exception;
use League\Tactician\CommandBus;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\EntityChangeRequestCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\LoadMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishProductionCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PushMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityMergeService;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\LoadEntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityActions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\EntityTypeFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\SamlEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\MetadataFetchException;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;

/**
 * The EntityControllerTrait contains the shared logic of the EntityCreateController and the EntityEditController.
 */
trait EntityControllerTrait
{
    public function __construct(
        private readonly CommandBus $commandBus,
        private readonly EntityService $entityService,
        private readonly ServiceService $serviceService,
        private readonly AuthorizationService $authorizationService,
        private readonly EntityTypeFactory $entityTypeFactory,
        private readonly LoadEntityService $loadEntityService,
        private readonly EntityMergeService $entityMergeService
    ) {
    }

    private function handleImport(Request $request, SaveSamlEntityCommand $command): FormInterface
    {
        // Handle an import action based on the posted xml or import url.
        $metadataCommand = new LoadMetadataCommand($command, $request->get('dashboard_bundle_entity_type'));
        try {
            $this->commandBus->handle($metadataCommand);
        } catch (MetadataFetchException) {
            $this->addFlash('error', 'entity.edit.metadata.fetch.exception');
        } catch (ParserException $e) {
            $this->addFlash('error', 'entity.edit.metadata.parse.exception');
            // Also show the parser messages
            $this->addFlash('preformatted', $e->getMessage());
            foreach ($e->getParserErrors() as $error) {
                $this->addFlash('preformatted', $error->message);
            }
        } catch (InvalidArgumentException) {
            $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
        }

        $form = $this->createForm(SamlEntityType::class, $command);

        if ($command->getStatus() === Constants::STATE_PUBLISHED) {
            $form->remove('save');
        }

        return $form;
    }

    private function publishEntity(
        ?ManageEntity $entity,
        SaveEntityCommandInterface $saveCommand,
        bool $isPublishedProductionEntity,
        FlashBagInterface $flashBag
    ): RedirectResponse|FormInterface {
        try {
            // Merge the save command data into the ManageEntity
            $entity = $this->entityMergeService->mergeEntityCommand($saveCommand, $entity);
            $publishEntityCommand = $this->createPublishEntityCommandFromEntity($entity, $isPublishedProductionEntity);
            $this->commandBus->handle($publishEntityCommand);
        } catch (Exception $e) {
            $flashBag->add('error', 'entity.edit.error.publish');
            return $this->redirectToRoute('service_overview');
        }

        if ($entity->getEnvironment() === Constants::ENVIRONMENT_TEST && !$isPublishedProductionEntity) {
            $this->commandBus->handle(new PushMetadataCommand(Constants::ENVIRONMENT_TEST));
        }

        // A clone is saved in session temporarily, to be able to report which entity was removed on the reporting
        // page we will be redirecting to in a moment.
        $this->container->get('request_stack')->getSession()->set('published.entity.clone', clone $entity);

        if ($publishEntityCommand instanceof PublishEntityTestCommand) {
            return $this->redirectToRoute('entity_published_test');
        }

        try {
            $destination = $this->findDestinationForRedirect($publishEntityCommand);
            $parameters = $this->findParametersForRedirect($publishEntityCommand);
            return $this->redirectToRoute($destination, $parameters);
        } catch (InvalidArgumentException $e) {
            $flashBag->add('error', $e->getMessage());
            return $this->redirectToRoute('service_overview');
        }
    }

    /**
     * @throws InvalidArgumentException
     */
    private function createPublishEntityCommandFromEntity(
        ?ManageEntity $entity,
        bool $isEntityChangeRequest,
    ): PublishEntityTestCommand|EntityChangeRequestCommand|PublishEntityProductionCommand {
        switch (true) {
            case $entity->getEnvironment() === Constants::ENVIRONMENT_TEST:
                $publishEntityCommand = new PublishEntityTestCommand($entity);
                break;
            case $isEntityChangeRequest:
                $applicant = $this->authorizationService->getContact();
                $publishEntityCommand = new EntityChangeRequestCommand($entity, $applicant);
                break;
            case $entity->getEnvironment() === Constants::ENVIRONMENT_PRODUCTION:
                $applicant = $this->authorizationService->getContact();
                $publishEntityCommand = new PublishEntityProductionCommand($entity, $applicant);
                break;
            default:
                throw new InvalidArgumentException(
                    sprintf('The environment with value "%s" is not supported.', $entity->getEnvironment())
                );
        }

        return $publishEntityCommand;
    }

    private function allowToRedirectToCreateConnectionRequest(PublishProductionCommandInterface $publishEntityCommand): bool
    {
        if (!($publishEntityCommand instanceof PublishEntityProductionCommand)) {
            return false;
        }
        $manageEntity = $publishEntityCommand->getManageEntity();

        $entityActions = new EntityActions(
            $manageEntity->getId(),
            $manageEntity->getService()->getId(),
            $manageEntity->getStatus(),
            $manageEntity->getEnvironment(),
            $manageEntity->getProtocol()->getProtocol(),
            false,
            false
        );

        return $entityActions->allowCreateConnectionRequestAction();
    }

    private function findParametersForRedirect(PublishProductionCommandInterface $publishEntityCommand): array
    {
        if ($this->allowToRedirectToCreateConnectionRequest($publishEntityCommand)) {
            $manageEntity = $publishEntityCommand->getManageEntity();
            return [
                'serviceId' => $manageEntity->getService()->getId(),
                'manageId' => $manageEntity->getId(),
                'environment' => $manageEntity->getEnvironment(),
            ];
        }
        return [];
    }

    private function findDestinationForRedirect(PublishProductionCommandInterface $publishEntityCommand): string
    {
        switch (true) {
            case $publishEntityCommand instanceof EntityChangeRequestCommand:
                return 'entity_change_request';
            case $publishEntityCommand instanceof PublishEntityProductionCommand:
                if ($publishEntityCommand->getManageEntity()->getStatus() !== Constants::STATE_PUBLICATION_REQUESTED
                && $this->allowToRedirectToCreateConnectionRequest($publishEntityCommand)
                ) {
                    return 'entity_published_create_connection_request';
                }
                return 'entity_published_production';
            default:
                throw new InvalidArgumentException(
                    sprintf('The environment with value "%s" is not supported.', $publishEntityCommand->getManageEntity()->getEnvironment())
                );
        }
    }

    /**
     * Check if the form was submitted using the given button name.
     */
    private function assertUsedSubmitButton(FormInterface $form, string $expectedButtonName): bool
    {
        $button = $form->getClickedButton();

        if ($button === null) {
            return false;
        }

        return $button->getName() === $expectedButtonName;
    }

    private function isImportAction(FormInterface $form): bool
    {
        return $this->assertUsedSubmitButton($form, 'importButton');
    }

    /**
     * The default action occurs when the user presses ENTER in a form.
     */
    private function isDefaultAction(FormInterface$form): bool
    {
        return $this->assertUsedSubmitButton($form, 'default');
    }

    private function isPublishAction(FormInterface$form): bool
    {
        if ($this->assertUsedSubmitButton($form, 'publishButton')) {
            return true;
        }

        return !$form->has('save') && $this->isDefaultAction($form);
    }

    private function isCancelAction(FormInterface$form): bool
    {
        return $this->assertUsedSubmitButton($form, 'cancel');
    }
}
