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
namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Constants;
use Surfnet\ServiceProviderDashboard\Domain\Entity\ManageEntity;
use Symfony\Component\Routing\RouterInterface;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class Entity
{
    private readonly \Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityActions $actions;

    /**
     * @param                                          string $id
     * @param                                          string $entityId
     * @param                                          int    $serviceId
     * @param                                          string $name
     * @param                                          string $contact
     * @param                                          string $state
     * @param                                          string $environment
     * @param                                          string $protocol
     * @param                                          bool   $isReadOnly
     * @param                                          bool   $hasChangeRequests
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private $id,
        private $entityId,
        $serviceId,
        private $name,
        private $contact,
        private $state,
        private $environment,
        private $protocol,
        $isReadOnly,
        $hasChangeRequests,
        private readonly RouterInterface $router
    ) {
        $this->actions = new EntityActions(
            $this->id,
            $serviceId,
            $this->state,
            $this->environment,
            $this->protocol,
            $isReadOnly,
            $hasChangeRequests
        );
    }

    /**
     * @return Entity
     */
    public static function fromManageTestResult(
        ManageEntity $result,
        RouterInterface $router,
        int $serviceId
    ): self {
        $formattedContact = self::formatManageContact($result);
        $protocol = $result->getProtocol()->getProtocol();
        return new self(
            $result->getId(),
            $result->getMetaData()->getEntityId(),
            $serviceId,
            $result->getMetaData()->getNameEn(),
            $formattedContact,
            $result->getStatus(),
            'test',
            $protocol,
            false,
            false,
            $router
        );
    }

    /**
     * @return Entity
     */
    public static function fromManageProductionResult(
        ManageEntity $result,
        RouterInterface $router,
        int $serviceId,
        bool $hasChangeRequests
    ): self {
        $formattedContact = self::formatManageContact($result);

        // As long as the coin:exclude_from_push metadata is present, allow modifications to the entity by
        // copying it from manage and merging the changes. The view status text: requested is set when an entity
        // can still be edited.
        $status = $result->getStatus();

        $excludeFromPush = $result->getMetaData()->getCoin()->getExcludeFromPush();
        if ($excludeFromPush === 1) {
            $status = Constants::STATE_PUBLICATION_REQUESTED;
        }
        $protocol = $result->getProtocol()->getProtocol();
        return new self(
            $result->getId(),
            $result->getMetaData()->getEntityId(),
            $serviceId,
            $result->getMetaData()->getNameEn(),
            $formattedContact,
            $status,
            'production',
            $protocol,
            false,
            $hasChangeRequests,
            $router
        );
    }

    /**
     * @return string
     */
    private static function formatManageContact(ManageEntity $metadata): string
    {
        $administrative = $metadata->getMetaData()->getContacts()->findAdministrativeContact();
        if ($administrative !== null) {
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

    public function isPublishedToProduction(): bool
    {
        return $this->state == 'published' && $this->environment == 'production';
    }

    /**
     * @return bool
     */
    public function isPublished(): bool
    {
        return $this->getState() === 'published';
    }

    /**
     * @return bool
     */
    public function isRequested(): bool
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
    public function getActions(): \Surfnet\ServiceProviderDashboard\Application\ViewObject\EntityActions
    {
        return $this->actions;
    }
}
