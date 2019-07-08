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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Form\Service;

use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Command\Service\SelectServiceCommand;
use Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Service\AuthorizationService;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ServiceSwitcherType extends AbstractType
{
    /**
     * @var AuthorizationService
     */
    private $authorizationService;

    public function __construct(AuthorizationService $authorizationService)
    {
        $this->authorizationService = $authorizationService;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $list = $this->authorizationService->getAllowedServiceNamesById();
        if (count($list) <= 1) {
            return '';
        }

        $selected = $this->authorizationService->getActiveServiceId();

        $builder->add(
            'selected_service_id',
            ChoiceType::class,
            [
                'choices' => array_flip($list),
                'expanded' => false,
                'multiple' => false,
                'data' => $selected,
                'required' => false,
            ]
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => SelectServiceCommand::class
        ));
    }
}
