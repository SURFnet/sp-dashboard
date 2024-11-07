<?php

/**
 * Copyright 2024 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\Invite;

use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Domain\Repository\Invite\DeleteInviteRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Invite\InviteDeleteRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Invite\InviteHttpClient;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class DeleteInviteRepositoryTest extends TestCase
{
    private DeleteInviteRepository $deleteInviteRepository;
    private InviteHttpClient $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(InviteHttpClient::class);
        $this->deleteInviteRepository = new InviteDeleteRepository($this->httpClient, new NullLogger());
    }

    public function testSuccessfulRoleDeletion(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_OK);

        $this->httpClient
            ->expects($this->once())
            ->method('delete')
            ->with('/external/v1/roles/123')
            ->willReturn($response);

        $this->deleteInviteRepository->deleteRole(123);
    }

    public function testNon200ResponseThrowsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_BAD_REQUEST);

        $this->httpClient
            ->method('delete')
            ->willReturn($response);

        $this->expectException(InviteException::class);
        $this->expectExceptionMessage('Could not delete Role. Invite returned non 200 status code "400"');

        $this->deleteInviteRepository->deleteRole(1);
    }

}