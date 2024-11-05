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

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Surfnet\ServiceProviderDashboard\Infrastructure\Invite\InviteHttpClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Invite\SendInviteRepository;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\ResponseInterface;

class SendInviteRepositoryTest extends TestCase
{
    private SendInviteRepository $repository;
    private InviteHttpClient $httpClient;

    private DateTimeImmutable $now;

    protected function setUp(): void
    {
        $this->httpClient = $this->createMock(InviteHttpClient::class);
        $this->repository = new SendInviteRepository($this->httpClient, new NullLogger());
        $this->now = new DateTimeImmutable();
    }

    public function testSuccessfulInviteCreation(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_CREATED);
        $response->method('toArray')->willReturn(
            json_decode(
                '{
  "status": 201,
  "recipientInvitationURLs": [
    {
      "recipient": "admin@service.nl",
      "invitationURL": "https://invite.test.surfconext.nl/invitation/accept?{hash}"
    }
  ]
}',
                true,
                512,
                JSON_THROW_ON_ERROR
            )
        );

        $this->httpClient
            ->expects($this->once())
            ->method('post')
            ->with(
                '/internal/invite/invitations',
                $this->callback(function ($payload) {
                    $expectedValues = [
                        'intendedAuthority' => 'INVITER',
                        'message' => 'Hallo, message.',
                        'language' => 'nl',
                        'guestRoleIncluded' => true,
                        'invites' => ['admin@service.nl'],
                        'roleIdentifiers' => [99],
                    ];

                    foreach ($expectedValues as $key => $value) {
                        if (!isset($payload[$key]) || $payload[$key] !== $value) {
                            return false;
                        }
                    }

                    return true;
                })
            )
            ->willReturn($response);

        $result = $this->repository->sendInvite(
            'admin@service.nl',
            'Hallo, message.',
            'nl',
            99,
        );

        $this->assertEquals('admin@service.nl', $result->recipient);
        $this->assertEquals('https://invite.test.surfconext.nl/invitation/accept?{hash}', $result->invitationURL);
    }

    public function testBadRequestThrowsException(): void
    {
        $response = $this->createMock(ResponseInterface::class);
        $response->method('getStatusCode')->willReturn(Response::HTTP_BAD_REQUEST);

        $this->httpClient
            ->method('post')
            ->willReturn($response);

        $this->expectException(InviteException::class);
        $this->expectExceptionMessage('Unable to send invite for "admin@service.nl". Bad request.');

        $this->repository->sendInvite(
            'admin@service.nl',
            'Hallo, message.',
            'nl',
            99,
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
        $this->expectExceptionMessage('Unable to send invite for admin@service.nl. Error code "500"');

        $this->repository->sendInvite(
            'admin@service.nl',
            'Hallo, message.',
            'nl',
            99,
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
        $this->expectExceptionMessage('Unable to send invite for admin@service.nl, invalid response');

        $this->repository->sendInvite(
            'admin@service.nl',
            'Hallo, message.',
            'nl',
            99,
        );
    }
}