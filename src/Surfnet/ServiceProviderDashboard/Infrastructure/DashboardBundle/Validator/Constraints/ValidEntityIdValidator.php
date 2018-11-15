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
    private $manageTestRepository;

    /**
     * @var QueryEntityRepository
     */
    private $manageProductionRepository;

    /**
     * @var EntityRepository
     */
    private $doctrineRepository;

    /**
     * @param QueryEntityRepository $manageTestRepository
     * @param QueryEntityRepository $manageProductionRepository
     * @param DoctrineRepository $doctrineRepository
     */
    public function __construct(
        QueryEntityRepository $manageTestRepository,
        QueryEntityRepository $manageProductionRepository,
        DoctrineRepository $doctrineRepository
    ) {
        $this->manageTestRepository = $manageTestRepository;
        $this->manageProductionRepository = $manageProductionRepository;
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
            $this->context->addViolation('validator.entity_id.invalid_url');
            return;
        }

        try {
            $parser->parseUrl($value);
        } catch (\Exception $e) {
            $this->context->addViolation('validator.entity_id.invalid_entity_id');
            return;
        }

        $manage = $this->manageTestRepository;
        if ($entityCommand->isForProduction()) {
            $manage = $this->manageProductionRepository;
        }

        try {
            $manageId = $manage->findManageIdByEntityId($value);
        } catch (\Exception $e) {
            $this->context->addViolation('validator.entity_id.registry_failure');
            return;
        }

        // Prevent publishing entities with existing entityId in Manage.
        if ($manageId && (!$entityCommand->getManageId() || $manageId !== $entityCommand->getManageId())) {
            $this->context->addViolation('validator.entity_id.already_exists');
        }
    }
}
