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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Entity;

use Surfnet\ServiceProviderDashboard\Application\Service\EntityAclService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\IdentityProvider;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AclListType extends AbstractType
{
    /**
     * @var EntityAclService
     */
    private $entityAclService;

    public function __construct(EntityAclService $entityAclService)
    {
        $this->entityAclService = $entityAclService;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $list = $this->entityAclService->getAvailableIdps();
        $resolver->setDefaults([
            'choices' => $list,
            'choice_label' => function (IdentityProvider $idp) {
                return $idp->getName();
            },
            'choice_value' => function (IdentityProvider $idp) {
                return $idp->getManageId();
            },
            'choice_name' => function (IdentityProvider $idp) {
                return $idp->getManageId();
            },
            'expanded' => true,
            'multiple' => true,
        ]);
    }

    public function getParent()
    {
        return ChoiceType::class;
    }
}
