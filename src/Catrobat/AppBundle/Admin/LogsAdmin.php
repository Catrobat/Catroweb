<?php
namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

class LogsAdmin extends Admin
{
    protected $baseRoutePattern = 'logs';
    protected $baseRouteName = 'logs';

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list'));
        $collection->add("apk")
            ->add("extracted")
            ->add("backup");
    }
}