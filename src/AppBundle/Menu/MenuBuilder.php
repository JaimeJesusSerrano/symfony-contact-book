<?php
namespace AppBundle\Menu;


use Knp\Menu\FactoryInterface;
use Symfony\Component\DependencyInjection\ContainerAware;

class MenuBuilder extends ContainerAware
{
    public function mainMenu(FactoryInterface $factory, array $options)
    {  	
        $menu = $factory->createItem('root');
        $menu->setChildrenAttribute('class', 'nav navbar-nav');

        self::mainMenuSharedContent($menu);

        return $menu;
    }
    
    private function mainMenuSharedContent($menu) 
    {
        $menu->addChild(
            'Add a new contact',
            array('route' => 'create_contact'))
        ->setAttribute('icon', 'fa fa-list');
		
        $menu->addChild(
            'Groups',
            array('route' => 'groups'))
        ->setAttribute('icon', 'fa fa-list');
    }
}