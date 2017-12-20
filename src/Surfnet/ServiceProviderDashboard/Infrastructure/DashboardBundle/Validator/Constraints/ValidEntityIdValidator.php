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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use Pdp\Parser;
use Pdp\PublicSuffixListManager;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommand;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository as DoctrineRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\QueryEntityRepository;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class ValidEntityIdValidator extends ConstraintValidator
{
    /**
     * @var QueryEntityRepository
     */
    private $manageRepository;

    /**
     * @var EntityRepository
     */
    private $doctrineRepository;

    /**
     * @param QueryEntityRepository $manageRepository
     * @param DoctrineEntityRepository $doctrineRepository
     */
    public function __construct(
        QueryEntityRepository $manageRepository,
        DoctrineRepository $doctrineRepository
    ) {
        $this->manageRepository = $manageRepository;
        $this->doctrineRepository = $doctrineRepository;
    }

    /**
     * @param string     $value
     * @param Constraint $constraint
     *
     * @SuppressWarnings(PHPMD.ElseExpression)
     */
    public function validate($value, Constraint $constraint)
    {
        $root = $this->context->getRoot();

        if ($root instanceof SaveEntityCommand) {
            $entityCommand = $root;
        } else {
            $entityCommand = $root->getData();
        }

        $metadataUrl = $entityCommand->getMetadataUrl();

        if (empty($metadataUrl) || empty($value)) {
            return;
        }

        $pslManager = new PublicSuffixListManager();
        $parser = new Parser($pslManager->getList());

        try {
            $parser->parseUrl($metadataUrl);
        } catch (\Exception $e) {
            $this->context->addViolation('Invalid metadataUrl.');
            return;
        }

        try {
            $parser->parseUrl($value);
        } catch (\Exception $e) {
            $this->context->addViolation('Invalid entityId.');
            return;
        }

        if ($entityCommand->isForProduction()) {
            return;
        }

        try {
            $manageId = $this->manageRepository->findManageIdByEntityId($value);
        } catch (\Exception $e) {
            $this->context->addViolation('Failed checking registry.');
            return;
        }

        // When the entity is not yet persisted, do not check for entity id violations
        if (!is_null($entityCommand->getId())) {
            $entity = $this->doctrineRepository->findById($entityCommand->getId());
            // Add a violation if the entity ID already exists, except when it is
            // used for the entity we are editing.
            if ($manageId && $manageId !== $entity->getManageId()) {
                $this->context->addViolation('Entity has already been registered.');
            }
        } else if ($manageId) {
            $this->context->addViolation('Entity has already been registered.');
        }
    }
}
