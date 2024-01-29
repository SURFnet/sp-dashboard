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
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOauthClientCredentialClientCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveOidcngResourceServerEntityCommand;
use Surfnet\ServiceProviderDashboard\Application\Parser\OidcngClientIdParser;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Service\ManageQueryService;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class UniqueEntityIdValidator extends ConstraintValidator
{
    public function __construct(private readonly ManageQueryService $queryService)
    {
    }

    /**
     * @param  string     $value
     * @param  Constraint $constraint
     * @throws Exception
     */
    public function validate($value, Constraint $constraint): void
    {
        $entityCommand = $this->context->getRoot()->getData();

        if (!($entityCommand instanceof SaveEntityCommandInterface)) {
            throw new Exception('invalid validator command exception');
        }

        $mode = $entityCommand->isForProduction() ? 'production' : 'test';

        if ($entityCommand instanceof SaveOidcngEntityCommand
            || $entityCommand instanceof SaveOidcngResourceServerEntityCommand
            || $entityCommand instanceof SaveOauthClientCredentialClientCommand
        ) {
            // Remove the protocol to ensure we can lookup the Oidc TNG entities for existence
            $value = OidcngClientIdParser::parse($value);
        }

        try {
            $manageId = $this->queryService->findManageIdByEntityId($mode, $value);
        } catch (Exception) {
            $this->context->addViolation('validator.entity_id.registry_failure');
            return;
        }

        // Prevent publishing entities with existing entityId in Manage.
        if ($manageId && $entityCommand->getManageId() !== $manageId) {
            $this->context->addViolation('validator.entity_id.already_exists');
        }
    }
}
