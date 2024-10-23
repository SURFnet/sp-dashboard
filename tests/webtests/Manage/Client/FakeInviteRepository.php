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

namespace Surfnet\ServiceProviderDashboard\Webtests\Manage\Client;

use RuntimeException;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Contact;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\InviteRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PublishEntityRepository as PublishEntityRepositoryInterface;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\CreateRoleResponse;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Uid\Uuid;

class FakeInviteRepository implements InviteRepository
{
    private string $path = __DIR__ . '/../../../../var/webtest-invite-repository.json';

    public function reset()
    {
        $this->write([]);

        // This code is run as root in the container by the test runner.
        // But when the webtest submits the form, the php process is owned by the www-data user.
        // So the www-data user needs write access to the fixture database
        exec(sprintf('chgrp www-data %s', realpath($this->path)));
        exec(sprintf('chmod g+w %s', realpath($this->path)));
    }

    public function registerPublishResponse(string $entityId, string $response)
    {
        $data = $this->read();
        if(isset($data[$entityId])){
            $responseArray = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            throw new InviteException(
                sprintf('The name "%s" already exists, please use a unique name.', $responseArray['name'])
            );
        }
        $data[$entityId] = $response;
        $this->write($data);
    }


    public function createRole(
        string $name,
        string $shortName,
        string $description,
        string $landingPage,
        string $manageId,
    ): CreateRoleResponse {
        $uuid = Uuid::v4()->toRfc4122();

        $data = $this->responseTemplate();
        $data['urn'] = 'urn:mace:surf.nl:test.surfaccess.nl:'.$uuid.':required_role_name';
        $data['identifier'] = $uuid;

        $this->registerPublishResponse($manageId, json_encode($data, JSON_THROW_ON_ERROR));


        return new CreateRoleResponse($data['id'], $data['name'], $data['shortName'], $data['description'], $data['urn']);
    }

    private function read()
    {
        return json_decode(file_get_contents($this->path), true);
    }

    private function write(array $data): void
    {
        file_put_contents($this->path, json_encode($data));
    }

    private function responseTemplate(){
        return json_decode(
            file_get_contents(__DIR__ . '/../../fixtures/invite-role-create.json'),
            true,
            512,
            JSON_THROW_ON_ERROR
        );
    }
}
