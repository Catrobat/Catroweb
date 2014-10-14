<?php
namespace Catrobat\AppBundle\Admin;

use Doctrine\DBAL\Query\QueryBuilder;
use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Entity\User;
use Sonata\AdminBundle\Route\RouteCollection;

class UploadNotificationAdmin extends Admin
{

    protected $baseRouteName = 'admin_catrobat_adminbundle_uploadnotificationadmin';
    protected $baseRoutePattern = 'upload_notification';

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere(
            $query->expr()->eq($query->getRootAlias() . '.upload_notification', ':notify_filter')
        );
        $query->setParameter('notify_filter', 'false');
        return $query;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {

        $formMapper
            ->add('user', 'entity', array('class' => 'Catrobat\AppBundle\Entity\User',
                'query_builder' => function (\Doctrine\ORM\EntityRepository $repository)
                {
                    return $repository->createQueryBuilder('u')
                        ->where('u.roles LIKE ?1')
                        ->setParameter(1, '%ADMIN%');
                })
            );
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('username')
            ->add('email')
        ;
    }

    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
    }
}

