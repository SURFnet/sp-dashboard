<?php

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class EntityListController
{
    /**
     * @Method("GET")
     * @Route("/", name="entity_list")
     * @Template()
     */
    public function listAction()
    {
        return [
            'text' => 'TODO',
        ];
    }
}
