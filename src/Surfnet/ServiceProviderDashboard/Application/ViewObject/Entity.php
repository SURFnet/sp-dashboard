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
    private readonly EntityActions $actions;

    /**
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        private readonly string          $id,
        private readonly string          $entityId,
        int                              $serviceId,
        private readonly string          $name,
        private readonly string          $contact,
        private readonly string          $state,
        private readonly string          $environment,
        private readonly string          $protocol,
        bool                             $isReadOnly,
        bool                             $hasChangeRequests,
        private readonly RouterInterface $router,
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

    public static function fromManageTestResult(
        ManageEntity $result,
        RouterInterface $router,
        int $serviceId,
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

    public static function fromManageProductionResult(
        ManageEntity $result,
        RouterInterface $router,
        int $serviceId,
        bool $hasChangeRequests,
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

    public function getId(): string
    {
        return $this->id;
    }

    public function getEntityId(): string
    {
        return $this->entityId;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getContact(): string
    {
        return $this->contact;
    }

    public function getState(): string
    {
        return $this->state;
    }

    public function getEnvironment(): string
    {
        return $this->environment;
    }

    public function getProtocol(): string
    {
        return $this->protocol;
    }

    public function isPublishedToProduction(): bool
    {
        return $this->state == 'published' && $this->environment == 'production';
    }

    public function isPublished(): bool
    {
        return $this->getState() === 'published';
    }

    public function isRequested(): bool
    {
        return $this->getState() === 'requested';
    }

    public function getLink(): string
    {
        return $this->router->generate(
            'entity_detail',
            [
                'id' => $this->getId(),
                'serviceId' => $this->getActions()->getServiceId(),
                'manageTarget' => $this->getEnvironment(),
            ]
        );
    }

    public function getActions(): EntityActions
    {
        return $this->actions;
    }
}
