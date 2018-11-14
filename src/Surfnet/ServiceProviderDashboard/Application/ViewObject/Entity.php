<?php

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
namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

use JsonSerializable;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity as DomainEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact as Contact;
use Symfony\Component\Routing\RouterInterface;

class Entity implements JsonSerializable
{
    /**
     * @var string
     */
    private $id;

    /**
     * @var string
     */
    private $entityId;

    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $contact;

    /**
     * @var string
     */
    private $state;

    /**
     * @var string
     */
    private $environment;
    /**
     * @var RouterInterface
     */
    private $router;

    /**
     * @param string $id
     * @param string $entityId
     * @param string $name
     * @param string $contact
     * @param string $state
     * @param string $environment
     * @param RouterInterface $router
     */
    public function __construct($id, $entityId, $name, $contact, $state, $environment, RouterInterface $router)
    {
        $this->id = $id;
        $this->entityId = $entityId;
        $this->name = $name;
        $this->contact = $contact;
        $this->state = $state;
        $this->environment = $environment;
        $this->router = $router;
    }

    public static function fromEntity(DomainEntity $entity, RouterInterface $router)
    {
        $contact = $entity->getAdministrativeContact();

        $formattedContact = '';

        if ($contact) {
            $formattedContact = self::formatDashboardContact($contact);
        }

        return new self(
            $entity->getId(),
            $entity->getEntityId(),
            $entity->getNameEn(),
            $formattedContact,
            $entity->getStatus(),
            $entity->getEnvironment(),
            $router
        );
    }

    public static function fromManageResult(array $result, RouterInterface $router)
    {
        $metadata = $result['data']['metaDataFields'];

        $formattedContact = self::formatManageContact($metadata);

        return new self(
            $result['id'],
            $result['data']['entityid'],
            $metadata['name:en'],
            $formattedContact,
            'published',
            'test',
            $router
        );
    }

    /**
     * @return string
     */
    private static function formatManageContact(array $metadata)
    {
        for ($i=0; $i<=2; $i++) {
            $attrPrefix = sprintf('contacts:%d:', $i);

            if (isset($metadata[$attrPrefix . 'contactType']) && $metadata[$attrPrefix . 'contactType'] === 'administrative') {
                return sprintf(
                    '%s %s (%s)',
                    $metadata[$attrPrefix . 'givenName'],
                    $metadata[$attrPrefix . 'surName'],
                    $metadata[$attrPrefix . 'emailAddress']
                );
            }
        }

        return '';
    }

    /**
     * @return string
     */
    private static function formatDashboardContact(Contact $contact)
    {
        return sprintf(
            '%s %s (%s)',
            $contact->getFirstName(),
            $contact->getLastName(),
            $contact->getEmail()
        );
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getEntityId()
    {
        return $this->entityId;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getContact()
    {
        return $this->contact;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @return string
     */
    public function getEnvironment()
    {
        return $this->environment;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->router->generate('entity_edit', ['id' => $this->getId()]);
    }

    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'environment' => $this->environment,
            'link' => $this->getLink(),
        ];
    }
}
