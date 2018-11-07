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
namespace Surfnet\ServiceProviderDashboard\Application\Service;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PrivacyQuestionsRepository;
use Surfnet\ServiceProviderDashboard\Domain\Repository\ServiceRepository;

class ServiceStatusService
{
    /**
     * @var PrivacyQuestionsRepository
     */
    private $privacyStatusRepository;

    public function __construct(PrivacyQuestionsRepository $privacyQuestionsRepository)
    {
        $this->privacyStatusRepository = $privacyQuestionsRepository;
    }

    /**
     * Test if the service has filled out privacy questions
     *
     * @param Service $service
     * @return bool
     */
    public function hasPrivacyQuestions(Service $service)
    {
        if ($this->privacyStatusRepository->findByService($service)) {
            // At some point, the privacy questions where answered (they might be all empty now but there is a record)
            return true;
        }
        return false;
    }
}
