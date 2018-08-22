<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Route\RouteCollection;
use Catrobat\AppBundle\Entity\Program;

class ApkListAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'admin_catrobat_apk_list';
    protected $baseRoutePattern = 'apk_list';

    protected $datagridValues = array(
        '_sort_by' => 'apk_request_time',
    );

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere(
            $query->expr()->eq($query->getRootAlias().'.apk_status', ':apk_status')
        );
        $query->setParameter('apk_status', Program::APK_READY);

        return $query;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('id')
            ->add('name')
            ->add('user.username')
            ->add('apk_request_time')
            ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('user', null, array(
                'route' => array(
                    'name' => 'show',
                ),
            ))
            ->add('name')
            ->add('apk_request_time')
            ->add('thumbnail', 'string', array('template' => ':Admin:program_thumbnail_image_list.html.twig'))
            ->add('apk_status', 'choice', array(
            'choices' => array(
                  Program::APK_NONE => 'none',
                  Program::APK_PENDING => 'pending',
                  Program::APK_READY => 'ready',
            ), ))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'Rebuild' => array(
                        'template' => ':CRUD:list__action_rebuild_apk.html.twig',
                    ),
                    'Delete Apk' => array(
                        'template' => ':CRUD:list__action_delete_apk.html.twig',
                    ),
                ),
            ))
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->clearExcept(array('list'));
        $collection->add('rebuildApk', $this->getRouterIdParameter().'/rebuildApk');
        $collection->add('deleteApk', $this->getRouterIdParameter().'/deleteApk');
    }

    public function getThumbnailImageUrl($object)
    {
        return '/'.$this->getConfigurationPool()->getContainer()->get('screenshotrepository')->getThumbnailWebPath($object->getId());
    }
}
