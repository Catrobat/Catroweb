<?php

namespace Catrobat\AppBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Catrobat\AppBundle\Forms\FeaturedImageConstraint;
use Sonata\CoreBundle\Model\Metadata;

class FeaturedUrlAdmin extends AbstractAdmin
{
    protected $baseRouteName = 'admin_featured_url';
    protected $baseRoutePattern = 'featured_url';

    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        $query->andWhere(
            $query->expr()->isNull($query->getRootAlias().'.program')
        );

        return $query;
    }

    // Fields to be shown on create/edit forms
    protected function configureFormFields(FormMapper $formMapper)
    {
        $file_options = array(
                'required' => ($this->getSubject()->getId() === null),
                'constraints' => array(
                   new FeaturedImageConstraint(),
               ), );
        if ($this->getSubject()->getId() != null) {
            $file_options['help'] = '<img src="../'.$this->getFeaturedImageUrl($this->getSubject()).'">';
        }

        $formMapper
            ->add('file', 'file', $file_options)
            ->add('url', 'url')
            ->add('flavor')
            ->add('priority')
            ->add('for_ios', null, array('label' => 'iOS only', 'required' => false, 'help' => 'Toggle for iOS featured url api call.'))
            ->add('active', null, array('required' => false))
            ;
    }

    // Fields to be shown on filter forms
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('url')
        ;
    }

    // Fields to be shown on lists
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('id')
            ->add('Featured Image', 'string', array('template' => ':Admin:featured_image.html.twig'))
            ->add('url', 'url')
            ->add('flavor', 'string', array('editable' => true))
            ->add('priority', 'integer', array('editable' => true))
            ->add('for_ios', null, array('label' => 'iOS only', 'editable' => true))
            ->add('active', null, array('editable' => true))
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                ),
            ))
            ;
    }

    public function getFeaturedImageUrl($object)
    {
        return '../../'.$this->getConfigurationPool()->getContainer()->get('featuredimagerepository')->getWebPath($object->getId(), $object->getImageType());
    }

    public function getObjectMetadata($object)
    {
        return new Metadata($object->getUrl(), '', $this->getFeaturedImageUrl($object));
    }

    public function preUpdate($image)
    {
        $image->old_image_type = $image->getImageType();
        $image->setImageType(null);
    }
}
