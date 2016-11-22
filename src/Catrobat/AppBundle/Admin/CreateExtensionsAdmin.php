<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

class CreateExtensionsAdmin extends Admin
{
    protected $baseRoutePattern = 'create';
    protected $baseRouteName = 'create';

    //Find the implementation in the Controller-Folder
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list'));
        $collection->add("extensions");
    }
}