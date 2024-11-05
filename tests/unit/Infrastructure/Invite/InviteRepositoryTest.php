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
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Invite\InviteHttpClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Invite\CreateRoleRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class InviteRepositoryTest extends TestCase
{
    private CreateRoleRepository $repository;
    private InviteHttpClient $httpClient;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(InviteHttpClient::class);
        $this->repository = new CreateRoleRepository($this->httpClient, new NullLogger());
    }

    public function testSuccessfulRoleCreation(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_CREATED);
        $response->method('toArray')->willReturn([
            'id' => '123',
            'name' => 'Test Role',
            'shortName' => 'test-role',
            'description' => 'Test Description',
            'urn' => 'urn:test:role'
        ]);

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                '/internal/invite/roles',
                $this->callback(function ($payload) {
                    return $payload['name'] === 'Test Role' &&
                        $payload['shortName'] === 'test-role' &&
                        $payload['description'] === 'Test Description';
                })
            )
            ->willReturn($response);

        $result = $this->repository->createRole(
            'Test Role',
            'test-role',
            'Test Description',
            'https://example.com',
            'manage-id-123'
        );

        $this->assertEquals('123', $result->id);
        $this->assertEquals('Test Role', $result->name);
        $this->assertEquals('test-role', $result->shortName);
        $this->assertEquals('Test Description', $result->description);
        $this->assertEquals('urn:test:role', $result->urn);
    }

    public function testConflictThrowsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_CONFLICT);

        $this->httpClient
            ->method('post')
            ->willReturn($response);

        $this->expectException(InviteException::class);
        $this->expectExceptionMessage('The name "Test Role" already exists, please use a unique name.');

        $this->repository->createRole(
            'Test Role',
            'test-role',
            'Test Description',
            'https://example.com',
            'manage-id-123'
        );
    }

    public function testBadRequestThrowsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_BAD_REQUEST);

        $this->httpClient
            ->method('post')
            ->willReturn($response);

        $this->expectException(InviteException::class);
        $this->expectExceptionMessage('Unable to create role for Test Role in invite due to a bad request.');

        $this->repository->createRole(
            'Test Role',
            'test-role',
            'Test Description',
            'https://example.com',
            'manage-id-123'
        );
    }

    public function testUnexpectedStatusCodeThrowsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_INTERNAL_SERVER_ERROR);

        $this->httpClient
            ->method('post')
            ->willReturn($response);

        $this->expectException(InviteException::class);
        $this->expectExceptionMessage('Unable to create role for Test Role in invite. Error code "500"');

        $this->repository->createRole(
            'Test Role',
            'test-role',
            'Test Description',
            'https://example.com',
            'manage-id-123'
        );
    }

    public function testInvalidResponseDataThrowsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_CREATED);
        $response->method('toArray')->willReturn(['invalid' => 'response']);

        $this->httpClient
            ->method('post')
            ->willReturn($response);

        $this->expectException(InviteException::class);
        $this->expectExceptionMessage('Unable to create role for Test Role in invite, invalid response');

        $this->repository->createRole(
            'Test Role',
            'test-role',
            'Test Description',
            'https://example.com',
            'manage-id-123'
        );
    }
}