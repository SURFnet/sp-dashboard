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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\DependencyInjection\Compiler;

use Exception;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\IssueFieldFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Factory\JiraServiceFactory;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Repository\DevelopmentIssueRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Jira\Repository\IssueRepository;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use function is_bool;

class IssueRepositoryCompilerPass implements CompilerPassInterface
{
    final public const ENABLE_TEST_MODE_FEATURE_FLAG = 'jira_enable_test_mode';
    final public const JIRA_REPOSITORY_ISSUE_SERVICE = 'surfnet.dashboard.repository.issue';

    /**
     * Based on the jira_enable_test_mode feature flag, will load the regular or test stand in for the IssueRepository.
     *
     * @param                                  ContainerBuilder $container
     * @SuppressWarnings(PHPMD.ElseExpression)
     * @throws                                 Exception
     */
    public function process(ContainerBuilder $container): void
    {
        $enableJiraTestMode = $container->getParameter(self::ENABLE_TEST_MODE_FEATURE_FLAG);
        $hasDefinition = $container->getDefinition(self::JIRA_REPOSITORY_ISSUE_SERVICE);
        if (!is_bool($enableJiraTestMode) && !$hasDefinition) {
            return;
        }

        if ($enableJiraTestMode) {
            $this->configureServiceInTestMode($container);
        } else {
            $this->configureService($container);
        }
    }

    /**
     * Configure the 'real' Jira repository
     */
    private function configureService(ContainerBuilder $container): void
    {
        $service = $container->getDefinition(self::JIRA_REPOSITORY_ISSUE_SERVICE);
        $service->setClass(IssueRepository::class);
        $service->setArguments(
            [
            $container->getDefinition(JiraServiceFactory::class),
            $container->getDefinition(IssueFieldFactory::class),
            $container->getParameter('env(jira_issue_project_key)'),
            $container->getParameter('env(jira_issue_type)'),
            $container->getParameter('env(jira_issue_manageid_fieldname)'),
            $container->getParameter('env(jira_issue_manageid_field_label)'),
            ]
        );
        $container->setDefinition(self::JIRA_REPOSITORY_ISSUE_SERVICE, $service);
    }

    /**
     * Configure the test stand-in Jira repository
     */
    private function configureServiceInTestMode(ContainerBuilder $container): void
    {
        $service = $container->getDefinition(self::JIRA_REPOSITORY_ISSUE_SERVICE);
        $service->setClass(DevelopmentIssueRepository::class);
        $service->setArguments(
            [
            $container->getParameter('env(jira_test_mode_storage_path)'),
            ]
        );
        $container->setDefinition(self::JIRA_REPOSITORY_ISSUE_SERVICE, $service);
    }
}
