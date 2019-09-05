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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto;

use Webmozart\Assert\Assert;

class Contact
{
    private $type;
    private $givenName;
    private $surName;
    private $email;
    private $phone;

    public static function from(array $contactData)
    {
        $type = $contactData['contactType'];
        $givenName = isset($contactData['givenName']) ? $contactData['givenName'] : '';
        $surName = isset($contactData['surName']) ? $contactData['surName'] : '';
        $email = isset($contactData['emailAddress']) ? $contactData['emailAddress'] : '';
        $phone = isset($contactData['phone']) ? $contactData['phone'] : '';

        Assert::stringNotEmpty($type);
        Assert::string($givenName);
        Assert::string($surName);
        Assert::string($email);
        Assert::string($phone);

        return new self($type, $givenName, $surName, $email, $phone);
    }

    /**
     * @param string $type
     * @param string $givenName
     * @param string $surName
     * @param string $email
     * @param string $phone
     */
    private function __construct($type, $givenName, $surName, $email, $phone)
    {
        $this->type = $type;
        $this->givenName = $givenName;
        $this->surName = $surName;
        $this->email = $email;
        $this->phone = $phone;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getGivenName()
    {
        return $this->givenName;
    }

    public function getSurName()
    {
        return $this->surName;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPhone()
    {
        return $this->phone;
    }
}
