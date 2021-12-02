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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Repository;

use Doctrine\ORM\EntityRepository as DoctrineEntityRepository;
use Surfnet\ServiceProviderDashboard\Domain\Entity\PrivacyQuestions;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Service;
use Surfnet\ServiceProviderDashboard\Domain\Repository\PrivacyQuestionsRepository
    as PrivacyQuestionsRepositoryInterface;

class PrivacyQuestionsRepository extends DoctrineEntityRepository implements PrivacyQuestionsRepositoryInterface
{
    public function save(PrivacyQuestions $questions)
    {
        $this->_em->persist($questions);
        $this->_em->flush($questions);
    }

    public function findByService(Service $service)
    {
        return parent::findOneBy([
            'service' => $service,
        ]);
    }
}
