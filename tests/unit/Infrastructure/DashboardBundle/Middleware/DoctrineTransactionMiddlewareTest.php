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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Middleware;

use Doctrine\ORM\EntityManager;
use Exception;
use League\Tactician\CommandBus;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Surfnet\ServiceProviderDashboard\Application\Command\Entity\SaveSamlEntityCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Middleware\DoctrineTransactionMiddleware;

class DoctrineTransactionMiddlewareTest extends MockeryTestCase
{
    private $entityManager;

    /**
     * @var DoctrineTransactionMiddleware
     */
    private $middleware;

    public function setUp()
    {
        $this->entityManager = m::mock(EntityManager::class);
        $this->middleware = new DoctrineTransactionMiddleware($this->entityManager);
    }

    /**
     * Simulation of a command that is executed without problem
     */
    public function test_command_execution()
    {
        $next = function ($command) {
            $bus = m::mock(CommandBus::class);
            $bus->shouldReceive('execute');
        };

        $command = m::mock(SaveSamlEntityCommand::class);
        $this->entityManager->shouldReceive('beginTransaction');
        $this->entityManager->shouldReceive('flush');
        $this->entityManager->shouldReceive('commit');

        $this->middleware->execute($command, $next);
    }


    /**
     * Simulation of a command that yields an exception
     */
    public function test_command_execution_raises_exception()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Beep');

        $next = function ($command) {
            throw new Exception('Beep');
        };

        $command = m::mock(SaveSamlEntityCommand::class);
        $this->entityManager->shouldReceive('beginTransaction');
        $this->entityManager->shouldReceive('rollback');

        $this->middleware->execute($command, $next);
    }
}
