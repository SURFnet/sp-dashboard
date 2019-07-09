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

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Console;

use Surfnet\ServiceProviderDashboard\Domain\Entity\Entity;
use Surfnet\ServiceProviderDashboard\Domain\Repository\EntityRepository;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Client\QueryClient;
use Surfnet\ServiceProviderDashboard\Infrastructure\Manage\Exception\QueryServiceProviderException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanOldProductionEntitiesCommand extends Command
{
    /**
     * @var EntityRepository
     */
    private $entityRepository;
    /**
     * @var QueryClient
     */
    private $productionManageClient;

    /**
     * @param EntityRepository $entityRepository
     * @param QueryClient $productionManageClient
     */
    public function __construct(EntityRepository $entityRepository, QueryClient $productionManageClient)
    {
        $this->entityRepository = $entityRepository;
        $this->productionManageClient = $productionManageClient;

        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('sp-dashboard:clear-old-entities')
            ->setDescription('Clears local manage production entities not known to Manage. No entities will be removed from manage.')
            ->setHelp('This command will clear local published production entities not known in to the production Manage. ' .
                'No entities will be removed from manage.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     * @throws QueryServiceProviderException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Start');
        do {
            $output->writeln('Process next batch');
        } while ($this->updateNextBatch($output));
        $output->write("Done\n");
    }

    /**
     * @param OutputInterface $output
     * @return bool
     * @throws QueryServiceProviderException
     */
    private function updateNextBatch(OutputInterface $output)
    {
        // fetch published production entities
        $clearEntities = $this->entityRepository->findByState(Entity::STATE_PUBLISHED, Entity::ENVIRONMENT_PRODUCTION);
        if (empty($clearEntities)) {
            return false;
        }

        // remove entities in queue
        /** @var Entity $entity */
        foreach ($clearEntities as $entity) {
            $output->write(sprintf("Delete entity: %s (local id)", $entity->getId()));
            if ($entity->getManageId()) {
                $output->write(sprintf("\t%s (manage)", $entity->getManageId()));
            }
            $output->writeln('');
            $this->entityRepository->delete($entity);
        }

        return true;
    }
}
