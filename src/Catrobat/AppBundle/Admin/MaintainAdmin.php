<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Route\RouteCollection;

class MaintainAdmin extends Admin
{
    protected $baseRoutePattern = 'maintain';
    protected $baseRouteName = 'maintain';

    //Find the implementation in the Controller-Folder
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list'));
        $collection->add("apk")
            ->add("extracted")
            ->add("delete_backups")
            ->add("create_backup")
            ->add("restore_backup")
            ->add("archive_logs")
            ->add("delete_logs");
    }
}