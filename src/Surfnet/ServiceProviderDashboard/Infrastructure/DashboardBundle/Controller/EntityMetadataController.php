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
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Legacy\Metadata\Generator;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EntityMetadataController extends Controller
{
    /**
     * @var Generator
     */
    private $generator;

    /**
     * @var ParserInterface
     */
    private $parser;

    /**
     * @var EntityService
     */
    private $entityService;

    /**
     * @param Generator $generator
     * @param ParserInterface $parser
     * @param EntityService $entityService
     */
    public function __construct(
        Generator $generator,
        ParserInterface $parser,
        EntityService $entityService
    ) {
        $this->generator = $generator;
        $this->parser = $parser;
        $this->entityService = $entityService;
    }

    /**
     * @Route("/entity/metadata/{entityId}", name="service_metadata")
     *
     * @param int $entityId
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function metadataAction($entityId)
    {
        $entity = $this->entityService->getEntityById($entityId);

        $xml = $this->generator->generate($entity);

        // Perform a sanity check on the generated metadata
        try {
            $this->parser->parseXml($xml);
        } catch (\Exception $e) {
            // TODO #151907601: This feature was put on the backlog
            //$this->get('mail.manager')->sendErrorNotification($entity, $xml, $e);
            //throw $e;
        }

        $response = new Response($xml);
        $response->headers->set('Content-Type', 'text/xml');

        return $response;
    }
}
