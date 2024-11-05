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

use Surfnet\ServiceProviderDashboard\Domain\Repository\Invite\SendInviteRepository;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\SendInviteResponse;
use Surfnet\ServiceProviderDashboard\Infrastructure\HttpClient\Exceptions\RuntimeException\InviteException;

class FakeSendInviteRepository implements SendInviteRepository
{
    private string $path = __DIR__ . '/../../../../var/webtest-send-invite-repository.json';

    public function reset()
    {
        $this->write([]);

        // This code is run as root in the container by the test runner.
        // But when the webtest submits the form, the php process is owned by the www-data user.
        // So the www-data user needs write access to the fixture database
        exec(sprintf('chgrp www-data %s', realpath($this->path)));
        exec(sprintf('chmod g+w %s', realpath($this->path)));
    }

    public function registerPublishResponse(string $email, string $response)
    {
        $data = $this->read();
        if(isset($data[$email])){
            $responseArray = json_decode($response, true, 512, JSON_THROW_ON_ERROR);
            throw new InviteException(
                sprintf('The name "%s" already exists, please use a unique name.', $responseArray['name'])
            );
        }
        $data[$email] = $response;
        $this->write($data);
    }

    public function count()
    {
        return count($this->read());
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
        );
    }

    public function sendInvite(
        string $email,
        string $message,
        string $language,
        int $roleIdentifier,
    ): SendInviteResponse {
        if($email === 'general@failure.com'){
            throw new InviteException(
                sprintf('Unable to send invite for "%s". Bad request.', $email)
            );
        }

        $data = $this->responseTemplate();
        $data['recipientInvitationURLs'][0]['recipient'] = $email;
        $this->registerPublishResponse($email, json_encode($data, JSON_THROW_ON_ERROR));


        return new SendInviteResponse($data['id'], $data['name'], $data['shortName'], $data['description'], $data['urn']);
    }
}
