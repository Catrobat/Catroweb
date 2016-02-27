<?php
/**
 * Created by IntelliJ IDEA.
 * User: catroweb
 * Date: 27.02.16
 * Time: 16:47
 */

namespace Catrobat\AppBundle\Admin;


use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

class EmailUserMessageAdmin extends Admin
{
    protected $baseRouteName = 'admin_mail';
    protected $baseRoutePattern = 'mail';

    protected function configureFormFields(FormMapper $formMapper)
    {

    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list'));
        $collection->add('send');
    }






}