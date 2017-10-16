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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Surfnet\ServiceProviderDashboard\Application\Metadata\GeneratorInterface;
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\SamlServiceService;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class ServiceMetadataController extends Controller
{
    /**
     * @var GeneratorInterface
     */
    private $generator;

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var SamlServiceService
     */
    private $samlService;

    /**
     * @param GeneratorInterface $generator
     * @param ParserInterface $parser
     * @param SamlServiceService $samlService
     */
    public function __construct(
        GeneratorInterface $generator,
        ParserInterface $parser,
        SamlServiceService $samlService
    ) {
        $this->generator = $generator;
        $this->parser = $parser;
        $this->samlService = $samlService;
    }

    /**
     * @Route("/service/metadata/{serviceId}", name="service_metadata")
     *
     * @param int $serviceId
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function metadataAction($serviceId)
    {
        $service = $this->samlService->getServiceById($serviceId);

        if ($service->isDraft()) {
            throw new BadRequestHttpException(
                'Service cannot be in draft when generating the Metadata'
            );
        }

        $xml = $this->generator->generate($service);

        // Perform a sanity check on the generated metadata
        try {
            $this->parser->parseXml($xml);
        } catch (\Exception $e) {
            // TODO #151907601: This feature was put on the backlog
            //$this->get('mail.manager')->sendErrorNotification($service, $xml, $e);
            //throw $e;
        }

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
