<?php

/**
 * Copyright 2018 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Tests\Unit\Infrastructure\DashboardBundle\Mailer;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\Mock;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityServiceInterface;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Security\Voter\ManageEntityAccessGrantedVoter;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

class ManageEntityAccessGrantedVoterTest extends MockeryTestCase
{
    /**
     * @var EntityServiceInterface|Mock
     */
    private $service;

    /**
     * @var ManageEntityAccessGrantedVoter
     */
    private $voter;

    public function setUp()
    {
        $this->service = m::mock(EntityServiceInterface::class);
        $this->voter = new ManageEntityAccessGrantedVoter($this->service);
    }

    /**
     * @dataProvider supports
     */
    public function test_it_only_supports_manage_entity_access_votes(
        $testName,
        $subject,
        $attributes,
        TokenInterface $token,
        $manageResponse,
        $expectation
    ) {


        $this->service
            ->shouldReceive('getManageEntityById')
            ->andReturn($manageResponse);

        $this->assertEquals(
            $expectation,
            $this->voter->vote($token, $subject, $attributes),
            sprintf('The Test with name "%s" failed', $testName)
        );
    }

    public function supports()
    {
        return [
            [
                'invalid_subject',
                ['teamName' => 'foobar'],
                [ManageEntityAccessGrantedVoter::MANAGE_ENTITY_ACCESS],
                $this->buildToken(['ROLE_USER']),
                $this->buildManageResponse(),
                VoterInterface::ACCESS_ABSTAIN,
            ],
            [
                'invalid_attributes',
                ['manageId' => 'id', 'environment' => 'test'],
                ['FOOBAR'],
                $this->buildToken(['ROLE_USER']),
                $this->buildManageResponse(),
                VoterInterface::ACCESS_ABSTAIN,
            ],
            [
                'invalid_roles',
                ['manageId' => 'id', 'environment' => 'test'],
                [ManageEntityAccessGrantedVoter::MANAGE_ENTITY_ACCESS],
                $this->buildToken([], false),
                $this->buildManageResponse(),
                VoterInterface::ACCESS_DENIED,
            ],
            [
                'invalid_manage_response',
                ['manageId' => 'id', 'environment' => 'test'],
                [ManageEntityAccessGrantedVoter::MANAGE_ENTITY_ACCESS],
                $this->buildToken(['ROLE_USER']),
                $this->buildManageResponse(false),
                VoterInterface::ACCESS_DENIED,
            ],
            [
                'non-existing-manage-entity',
                ['manageId' => 'id', 'environment' => 'test'],
                [ManageEntityAccessGrantedVoter::MANAGE_ENTITY_ACCESS],
                $this->buildToken(['ROLE_USER'], true),
                null,
                VoterInterface::ACCESS_DENIED,
            ],
            [
                'valid',
                ['manageId' => 'id', 'environment' => 'test'],
                [ManageEntityAccessGrantedVoter::MANAGE_ENTITY_ACCESS],
                $this->buildToken(['ROLE_USER'], true),
                $this->buildManageResponse(),
                VoterInterface::ACCESS_GRANTED,
            ],
            [
                'valid_admin',
                ['manageId' => 'id', 'environment' => 'test'],
                [ManageEntityAccessGrantedVoter::MANAGE_ENTITY_ACCESS],
                $this->buildToken(['ROLE_ADMINISTRATOR'], false),
                $this->buildManageResponse(),
                VoterInterface::ACCESS_GRANTED,
            ],
        ];
    }

    private function buildManageResponse($response = 'team-a')
    {
        $manageEntity = m::mock(ManageEntity::class);
        $manageEntity
            ->shouldReceive('getMetaData->getCoin->getServiceTeamId')
            ->andReturn($response);

        return $manageEntity;
    }

    private function buildToken(array $roles = [], $isPartOfTeam = true)
    {
        $token = m::mock(TokenInterface::class);
        $user = m::mock(TokenInterface::class);

        $token
            ->shouldReceive('getUser')
            ->andReturn($user);

        $token
            ->shouldReceive('hasRole')
            ->andReturnUsing(
                function ($role) use ($roles) {
                    return in_array($role, $roles);
                }
            );

        $user->shouldReceive('isPartOfTeam')
            ->andReturn($isPartOfTeam);

        return $token;
    }
}
