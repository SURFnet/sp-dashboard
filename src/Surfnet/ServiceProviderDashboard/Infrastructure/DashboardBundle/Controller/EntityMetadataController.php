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
use Surfnet\ServiceProviderDashboard\Application\Metadata\JsonGenerator;
use Surfnet\ServiceProviderDashboard\Application\Metadata\ParserInterface;
use Surfnet\ServiceProviderDashboard\Application\Service\EntityService;
use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class EntityMetadataController extends Controller
{
    /**
     * @var JsonGenerator
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
     * @param JsonGenerator $generator
     * @param ParserInterface $parser
     * @param EntityService $entityService
     */
    public function __construct(
        JsonGenerator $generator,
        ParserInterface $parser,
        EntityService $entityService
    ) {
        $this->generator = $generator;
        $this->parser = $parser;
        $this->entityService = $entityService;
    }

    /**
     * @Route("/entity/metadata/{entityId}", name="entity_metadata")
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

        if ($entity->isDraft()) {
            throw new BadRequestHttpException(
                'Service cannot be in draft when generating the Metadata'
            );
        }
        if ($entity->getProtocol() !== Entity::TYPE_SAML) {
            throw new BadRequestHttpException(
                'Only entities of type SAML have Metadata'
            );
        }

        return new JsonResponse(
            $this->generator->generateDataForNewEntity($entity)
        );
    }
}
