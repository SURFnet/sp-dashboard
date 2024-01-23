<?php

declare(strict_types = 1);

/**
 * Copyright 2017 SURFnet B.V.
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

namespace Surfnet\ServiceProviderDashboard\Domain\ValueObject;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity\Contact as ContactEntity;
use Symfony\Component\Validator\Constraints as Assert;
use Stringable;

class Contact implements Stringable
{
    /**
     * @var string
     */
    #[Assert\NotBlank(groups: ['Default', 'production'])]
    private $firstName;

    /**
     * @var string
     */
    #[Assert\NotBlank(groups: ['Default', 'production'])]
    private $lastName;

    #[Assert\NotBlank(groups: ['Default', 'production'])]
    private string|null|array $email = null;

    /**
     * @var string
     */
    private $phone;

    public static function from(?ContactEntity $contact): ?Contact
    {
        if ($contact instanceof ContactEntity) {
            $instance = new self;
            $instance->email = $contact->getEmail();
            $instance->firstName = $contact->getGivenName();
            $instance->lastName = $contact->getSurName();
            $instance->phone = $contact->getPhone();
            return $instance;
        }
        return null;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     *
     * @return Contact
     */
    public function setFirstName($firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    /**
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param string $lastName
     *
     * @return Contact
     */
    public function setLastName($lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string|array|null
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return Contact
     */
    public function setEmail($email): static
    {
        // Managa adds a mailto: prefix to the contact email address in the
        // metadata XML, we strip it as a workaround.
        $this->email = preg_replace('/^mailto:/', '', $email);

        return $this;
    }

    /**
     * @return string
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     *
     * @return Contact
     */
    public function setPhone($phone): static
    {
        $this->phone = $phone;

        return $this;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        $result = $this->firstName . ' ' . $this->lastName .' (' . $this->email;

        if (!empty($this->phone)) {
            $result .= ' / ' . $this->phone;
        }

        return $result . ')';
    }

    /**
     * Tests if any of the attributes of the contact are set
     *
     * If one of the fields is filled this method will return `true`, if none are initialized `false` is returned.
     *
     * As a suggestion for future improvement: create a NullContact VO that is instantiated instead of an empty Contact.
     */
    public function isContactSet(): bool
    {
        return !(
            empty($this->firstName)
            && empty($this->lastName)
            && ($this->email === '' || $this->email === '0' || $this->email === [] || $this->email === null)
            && empty($this->phone)
        );
    }
}
