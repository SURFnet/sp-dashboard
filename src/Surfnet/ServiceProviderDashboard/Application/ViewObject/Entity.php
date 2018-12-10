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

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity as DomainEntity;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact as Contact;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Dto\ManageEntity;
use Symfony\Component\Routing\RouterInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Entity
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
     * @var EntityActions
     */
    private $actions;

    /**
     * @param string $id
     * @param string $entityId
     * @param int $serviceId
     * @param string $name
     * @param string $contact
     * @param string $state
     * @param string $environment
     * @param string $protocol
     * @param RouterInterface $router
     */
    public function __construct(
        $id,
        $entityId,
        $serviceId,
        $name,
        $contact,
        $state,
        $environment,
        $protocol,
        RouterInterface $router
    ) {
        $this->id = $id;
        $this->entityId = $entityId;
        $this->name = $name;
        $this->contact = $contact;
        $this->state = $state;
        $this->environment = $environment;
        $this->protocol = $protocol;
        $this->router = $router;
        $this->actions = new EntityActions($id, $serviceId, $state, $environment);
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
            $entity->getService()->getId(),
            $entity->getNameEn(),
            $formattedContact,
            $entity->getStatus(),
            $entity->getEnvironment(),
            $entity->getProtocol(),
            $router
        );
    }

    /**
     * @param ManageEntity $result
     * @param RouterInterface $router
     * @param int $serviceId
     * @return Entity
     */
    public static function fromManageTestResult(ManageEntity $result, RouterInterface $router, $serviceId)
    {
        $formattedContact = self::formatManageContact($result);

        return new self(
            $result->getId(),
            $result->getMetaData()->getEntityId(),
            $serviceId,
            $result->getMetaData()->getNameEn(),
            $formattedContact,
            'published',
            'test',
            self::detectProtocol($result),
            $router
        );
    }

    /**
     * @param ManageEntity $result
     * @param RouterInterface $router
     * @param int $serviceId
     * @return Entity
     */
    public static function fromManageProductionResult(ManageEntity $result, RouterInterface $router, $serviceId)
    {
        $formattedContact = self::formatManageContact($result);

        // As long as the coin:exclude_from_push metadata is present, allow modifications to the entity by
        // copying it from manage and merging the changes. The view status text: requested is set when an entity
        // can still be edited.
        $status = 'published';

        $excludeFromPush = $result->getMetaData()->getCoin()->getExcludeFromPush();
        if ($excludeFromPush === 1) {
            $status = 'requested';
        }

        return new self(
            $result->getId(),
            $result->getMetaData()->getEntityId(),
            $serviceId,
            $result->getMetaData()->getNameEn(),
            $formattedContact,
            $status,
            'production',
            self::detectProtocol($result),
            $router
        );
    }

    /**
     * @return string
     */
    private static function formatManageContact(ManageEntity $metadata)
    {
        $administrative = $metadata->getMetaData()->getContacts()->findAdministrativeContact();
        if ($administrative) {
            return sprintf(
                '%s %s (%s)',
                $administrative->getGivenName(),
                $administrative->getSurName(),
                $administrative->getEmail()
            );
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
     * @param ManageEntity $entity
     * @return string
     */
    private static function detectProtocol(ManageEntity $entity)
    {
        if ($entity->getMetaData()->getCoin()->getOidcClient()) {
            return DomainEntity::TYPE_OPENID_CONNECT;
        }
        return DomainEntity::TYPE_SAML;
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
    public function getProtocol()
    {
        return $this->protocol;
    }

    public function isPublishedToProduction()
    {
        return $this->state == 'published' && $this->environment == 'production';
    }

    /**
     * @return bool
     */
    public function isPublished()
    {
        return $this->getState() === 'published';
    }

    /**
     * @return bool
     */
    public function isRequested()
    {
        return $this->getState() === 'requested';
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->router->generate(
            'entity_detail',
            [
                'id' => $this->getId(),
                'serviceId' => $this->getActions()->getServiceId(),
                'manageTarget' => $this->getEnvironment()
            ]
        );
    }

    /**
     * @return EntityActions
     */
    public function getActions()
    {
        return $this->actions;
    }
}
