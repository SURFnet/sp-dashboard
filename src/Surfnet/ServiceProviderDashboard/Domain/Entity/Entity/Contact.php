<?php

declare(strict_types = 1);

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

namespace Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;

use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact as ContactVO;
use Webmozart\Assert\Assert;

class Contact
{
    public static function from(array $contactData): self
    {
        $type = $contactData['contactType'];
        $givenName = $contactData['givenName'] ?? '';
        $surName = $contactData['surName'] ?? '';
        $email = $contactData['emailAddress'] ?? '';
        $phone = $contactData['telephoneNumber'] ?? '';

        Assert::stringNotEmpty($type);
        Assert::string($givenName);
        Assert::string($surName);
        Assert::string($email);
        Assert::string($phone);

        return new self($type, $givenName, $surName, $email, $phone);
    }

    public static function fromContact(ContactVO $contact, string $type): self
    {
        return new self(
            $type,
            $contact->getFirstName(),
            $contact->getLastName(),
            $contact->getEmail(),
            $contact->getPhone()
        );
    }

    private function __construct(
        private readonly string $type,
        private readonly ?string $givenName,
        private readonly ?string $surName,
        private readonly ?string $email,
        private readonly ?string $phone,
    ) {
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getGivenName(): ?string
    {
        return $this->givenName;
    }

    public function getSurName(): ?string
    {
        return $this->surName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }
}
