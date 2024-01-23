<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Application\ViewObject\Manage;

use Webmozart\Assert\Assert;

class Connection
{
    public function __construct(
        private readonly string $host,
        private readonly string $username,
        private readonly string $password,
    ) {
        Assert::stringNotEmpty($host, 'Please set the manage host in .env');
        Assert::stringNotEmpty($username, 'Please set the manage username in .env');
        Assert::stringNotEmpty($password, 'Please set the manage password in .env');
    }

    public function getHost(): string
    {
        return $this->host;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
