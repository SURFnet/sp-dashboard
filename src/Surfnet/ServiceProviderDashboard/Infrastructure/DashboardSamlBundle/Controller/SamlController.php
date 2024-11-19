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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardSamlBundle\Controller;

use Surfnet\SamlBundle\Exception\LogicException;
use Surfnet\SamlBundle\Http\XMLResponse;
use Surfnet\SamlBundle\Metadata\MetadataFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Attribute\Route;

class SamlController extends AbstractController
{
    public function __construct(
        private readonly MetadataFactory $metadataFactory,
    ) {
    }

    #[Route(path: '/saml/acs', name: 'dashboard_saml_consume_assertion', methods: ['POST'])]
    public function consumeAssertion() : never
    {
        throw new LogicException(
            'Unreachable statement, should be handled by the SAML firewall'
        );
    }

    #[Route(path: '/saml/metadata', name: 'dashboard_saml_metadata', methods: ['GET'])]
    public function metadata(): XMLResponse
    {
        return new XMLResponse(
            $this->metadataFactory->generate()
        );
    }
}
