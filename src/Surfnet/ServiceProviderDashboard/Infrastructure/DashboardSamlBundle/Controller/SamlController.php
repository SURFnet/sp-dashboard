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

use Psr\Log\LoggerInterface;
use Surfnet\SamlBundle\Entity\IdentityProvider;
use Surfnet\SamlBundle\Entity\ServiceProvider;
use Surfnet\SamlBundle\Exception\LogicException;
use Surfnet\SamlBundle\Http\PostBinding;
use Surfnet\SamlBundle\Http\XMLResponse;
use Surfnet\SamlBundle\Metadata\MetadataFactory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class SamlController extends AbstractController
{
    /**
     * @var LoggerInterface $logger
     */
    private $logger;

    /**
     * @var MetadataFactory $metadataFactory
     */
    private $metadataFactory;

    /**
     * @var PostBinding $postBinding
     */
    private $postBinding;

    /**
     * @var IdentityProvider $identityProvider
     */
    private $identityProvider;

    /**
     * @var ServiceProvider $serviceProvider
     */
    private $serviceProvider;

    public function __construct(
        LoggerInterface $logger,
        MetadataFactory $metadataFactory,
        PostBinding $postBinding,
        IdentityProvider $identityProvider,
        ServiceProvider $serviceProvider
    ) {
        $this->logger = $logger;
        $this->metadataFactory = $metadataFactory;
        $this->postBinding = $postBinding;
        $this->identityProvider = $identityProvider;
        $this->serviceProvider = $serviceProvider;
    }

    /**
     * @Route("/saml/acs", name="dashboard_saml_consume_assertion", methods={"POST"})
     * @param Request $request
     */
    public function consumeAssertionAction(Request $request)
    {
        throw new LogicException(
            'Unreachable statement, should be handled by the SAML firewall'
        );
    }

    /**
     * @Route("/saml/metadata", name="dashboard_saml_metadata", methods={"GET"})
     */
    public function metadataAction()
    {
        return new XMLResponse(
            $this->metadataFactory->generate()
        );
    }
}
