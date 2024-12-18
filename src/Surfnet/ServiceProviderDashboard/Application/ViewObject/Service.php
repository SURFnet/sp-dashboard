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
namespace Surfnet\ServiceProviderDashboard\Application\ViewObject;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service as DomainService;
use Symfony\Component\Routing\RouterInterface;

class Service
{
    public function __construct(
        private readonly string $id,
        private readonly string $name,
        private readonly bool $privacyQuestionsEnabled,
        private readonly EntityList $entityList,
        private readonly RouterInterface $router,
        private readonly ?int $inviteRoleId,
        private readonly bool $productionEntitiesEnabled = false,
    ) {
    }

    public static function fromService(DomainService $service, EntityList $entityList, RouterInterface $router): self
    {
        return new self(
            $service->getId(),
            $service->getName(),
            $service->isPrivacyQuestionsEnabled(),
            $entityList,
            $router,
            $service->getInviteRoleId(),
            $service->isProductionEntitiesEnabled()
        );
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getEntityList(): EntityList
    {
        return $this->entityList;
    }

    public function getLink(): string
    {
        return $this->router->generate('select_service', ['service' => $this->getId()]);
    }

    public function arePrivacyQuestionsEnabled(): bool
    {
        return $this->privacyQuestionsEnabled;
    }

    public function hasTestEntities() : bool
    {
        return $this->getEntityList()->hasTestEntities();
    }

    public function isProductionEntitiesEnabled(): bool
    {
        return $this->productionEntitiesEnabled || $this->hasTestEntities();
    }

    public function getInviteRoleId(): ?int
    {
        return $this->inviteRoleId;
    }
}
