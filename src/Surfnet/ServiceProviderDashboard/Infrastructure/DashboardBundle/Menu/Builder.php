<?php

namespace Surfnet\ServiceProviderDashboard\Infrastructure\DashboardBundle\Menu;

use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

class Builder implements ContainerAwareInterface
{
    use ContainerAwareTrait;

    public function mainMenu(FactoryInterface $factory, array $options)
    {
        $menu = $factory->createItem('root');

        $menu->addChild('Services', array('route' => 'entity_list'));

        $menu->addChild('Registration', array(
            'uri' => '/',
        ));

        return $menu;
    }
}
