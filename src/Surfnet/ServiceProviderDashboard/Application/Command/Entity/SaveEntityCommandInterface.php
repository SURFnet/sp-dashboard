<?php

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

namespace Surfnet\ServiceProviderDashboard\Application\Command\Entity;

use Surfnet\ServiceProviderDashboard\Application\Command\Command;
use Surfnet\ServiceProviderDashboard\Domain\ValueObject\Contact;

interface SaveEntityCommandInterface extends Command
{
    /**
     * @return bool
     */
    public function isForProduction();

    /**
     * @return string
     */
    public function getManageId();

    public function getTechnicalContact(): ?Contact;
    public function getAdministrativeContact(): ?Contact;
    public function getSupportContact(): ?Contact;

    public function getOrganizationNameEn(): ?string;
    public function getOrganizationDisplayNameEn(): ?string;
    public function getOrganizationUrlEn(): ?string;
    public function getOrganizationNameNl(): ?string;
    public function getOrganizationDisplayNameNl(): ?string;
    public function getOrganizationUrlNl(): ?string;

    public function getApplicationUrl(): ?string;
    public function getEulaUrl(): ?string;
    public function getCertificate(): ?string;
    public function getLogoUrl(): ?string;
    public function getComments(): ?string;
    public function getEnvironment(): ?string;
    public function getProtocol(): string;
}
