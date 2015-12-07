<?php
namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

class SnapshotAdmin extends Admin
{
    protected $baseRoutePattern = 'snapshots';
    protected $baseRouteName = 'snapshots';
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array(
            'list'
        ));
    }
}