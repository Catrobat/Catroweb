<?php
namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Route\RouteCollection;
use Catrobat\AppBundle\Entity\Program;

class PendingApkRequestsAdmin extends Admin
{
    protected $baseRouteName = 'admin_catrobat_apk_pending_requests';
    protected $baseRoutePattern = 'apk_pending_requests';

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere(
            $query->expr()->eq($query->getRootAlias() . '.apk_status', ':apk_status')
        );
        $query->setParameter('apk_status', Program::APK_PENDING);
        return $query;
    }
    
    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
            ->add('user')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('user', null, array(
                'route' => array(
                    'name' => 'show'
                )
            ))
            ->add('name')
            ->add('thumbnail', 'string', array('template' => ':Admin:program_thumbnail_image_list.html.twig'))
            ->add('apk_status', 'choice', array(
            'choices' => array(
                  Program::APK_NONE => 'none',
                  Program::APK_PENDING => 'pending',
                  Program::APK_READY => 'ready',
            )))
        ;
    }

  protected function configureRoutes(RouteCollection $collection)
  {
    $collection->clearExcept(array('list'));
  }

    public function getThumbnailImageUrl($object)
    {
      return "/".$this->getConfigurationPool()->getContainer()->get("screenshotrepository")->getThumbnailWebPath($object->getId());
    }
}

