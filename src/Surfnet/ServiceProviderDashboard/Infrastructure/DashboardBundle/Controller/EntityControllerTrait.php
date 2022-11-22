<?php

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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionAfterClientResetCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityProductionCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PublishEntityTestCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\PushMetadataCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommandInterface;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Exception\InvalidArgumentException;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityMergeService;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\LoadEntityService;
use Surfnet\ServiceProviderDashboard\Application\Service\ServiceService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Factory\EntityTypeFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity\SamlEntityType;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\MetadataFetchException;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Exception\ParserException;
use Symfony\Component\Form\Form;
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

    /**
     * @param Request $request
     * @param SaveSamlEntityCommand $command
     *
     * @return Form
     */
    private function handleImport(Request $request, SaveSamlEntityCommand $command): Form
    {
        // Handle an import action based on the posted xml or import url.
        $metadataCommand = new LoadMetadataCommand($command, $request->get('dashboard_bundle_entity_type'));
        try {
            $this->commandBus->handle($metadataCommand);
        } catch (MetadataFetchException $e) {
            $this->addFlash('error', 'entity.edit.metadata.fetch.exception');
        } catch (ParserException $e) {
            $this->addFlash('error', 'entity.edit.metadata.parse.exception');
            // Also show the parser messages
            $this->addFlash('preformatted', $e->getMessage());
            foreach ($e->getParserErrors() as $error) {
                $this->addFlash('preformatted', $error->message);
            }
        } catch (InvalidArgumentException $e) {
            $this->addFlash('error', 'entity.edit.metadata.invalid.exception');
        }

        $form = $this->createForm(SamlEntityType::class, $command);

        if ($command->getStatus() === Constants::STATE_PUBLISHED) {
            $form->remove('save');
        }

        return $form;
    }

    /**
     * @return RedirectResponse|Form
     */
    private function publishEntity(
        ?ManageEntity $entity,
        SaveEntityCommandInterface $saveCommand,
        bool $isEntityChangeRequest,
        FlashBagInterface $flashBag
    ): RedirectResponse|Form {
        // Merge the save command data into the ManageEntity
        $entity = $this->entityMergeService->mergeEntityCommand($saveCommand, $entity);

        switch (true) {
            case $entity->getEnvironment() === Constants::ENVIRONMENT_TEST:
                $publishEntityCommand = new PublishEntityTestCommand($entity);
                $destination = 'entity_published_test';
                break;
            case $isEntityChangeRequest:
                $applicant = $this->authorizationService->getContact();
                $publishEntityCommand = new EntityChangeRequestCommand($entity, $applicant);
                $destination = 'entity_change_request';
                break;
            case $entity->getEnvironment() === Constants::ENVIRONMENT_PRODUCTION:
                $applicant = $this->authorizationService->getContact();
                $publishEntityCommand = new PublishEntityProductionCommand($entity, $applicant);
                $destination = 'entity_published_production';
                break;
            default:
                throw new InvalidArgumentException(
                    sprintf('The environment with value "%s" is not supported.', $entity->getEnvironment())
                );
        }

        try {
            $this->commandBus->handle($publishEntityCommand);
        } catch (Exception $e) {
            $flashBag->add('error', 'entity.edit.error.publish');
        }

        if (!$flashBag->has('error')) {
            if ($entity->getEnvironment() === Constants::ENVIRONMENT_TEST && !$isEntityChangeRequest) {
                $this->commandBus->handle(new PushMetadataCommand(Constants::ENVIRONMENT_TEST));
            }

            // A clone is saved in session temporarily, to be able to report which entity was removed on the reporting
            // page we will be redirecting to in a moment.
            $this->get('session')->set('published.entity.clone', clone $entity);
        }
        return $this->redirectToRoute($destination);
    }

    /**
     * Check if the form was submitted using the given button name.
     *
     * @param Form $form
     * @param string $expectedButtonName
     * @return bool
     */
    private function assertUsedSubmitButton(Form $form, $expectedButtonName): bool
    {
        $button = $form->getClickedButton();

        if ($button === null) {
            return false;
        }

        return $button->getName() === $expectedButtonName;
    }

    /**
     * @param Form $form
     * @return bool
     */
    private function isImportAction(Form $form)
    {
        return $this->assertUsedSubmitButton($form, 'importButton');
    }

    /**
     * The default action occurs when the user presses ENTER in a form.
     *
     * @param Form $form
     * @return bool
     */
    private function isDefaultAction(Form $form)
    {
        return $this->assertUsedSubmitButton($form, 'default');
    }

    /**
     * @param Form $form
     * @return bool
     */
    private function isPublishAction(Form $form)
    {
        if ($this->assertUsedSubmitButton($form, 'publishButton')) {
            return true;
        }

        return !$form->has('save') && $this->isDefaultAction($form);
    }

    /**
     * @param Form $form
     * @return bool
     */
    private function isCancelAction(Form $form)
    {
        return $this->assertUsedSubmitButton($form, 'cancel');
    }
}
