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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Validator\Constraints;

use Exception;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveEntityCommandInterface;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository as DoctrineRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Service\MangeQueryService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEntityIdValidator extends ConstraintValidator
{
    /**
     * @var MangeQueryService
     */
    private $queryService;

    /**
     * @var DoctrineRepository
     */
    private $doctrineRepository;

    /**
     * @param DoctrineRepository $doctrineRepository
     */
    public function __construct(
        MangeQueryService $queryService,
        DoctrineRepository $doctrineRepository
    ) {
        $this->queryService = $queryService;
        $this->doctrineRepository = $doctrineRepository;
    }

    /**
     * @param string $value
     * @param Constraint $constraint
     * @throws Exception
     */
    public function validate($value, Constraint $constraint)
    {
        $entityCommand = $this->context->getRoot()->getData();

        if (!($entityCommand instanceof SaveEntityCommandInterface)) {
            throw new Exception('invalid validator command exception');
        }

        $mode = $entityCommand->isForProduction() ? 'production' : 'test';

        try {
            $manageId = $this->queryService->findManageIdByEntityId($mode, $value);
        } catch (Exception $e) {
            $this->context->addViolation('validator.entity_id.registry_failure');
            return;
        }

        // Prevent publishing entities with existing entityId in Manage.
        if ($manageId && $entityCommand->getManageId() !== $manageId) {
            $this->context->addViolation('validator.entity_id.already_exists');
        }
    }
}
