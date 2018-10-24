<?php

namespace Catrobat\AppBundle\Admin;

use Catrobat\AppBundle\Entity\MediaPackageCategory;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class MediaPackageFileAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'adminmedia_package_file';
  protected $baseRoutePattern = 'media_package_file';

  // Fields to be shown on create/edit forms
  protected function configureFormFields(FormMapper $formMapper)
  {
    $file_options = [
      'required' => ($this->getSubject()->getId() === null),];
//        if ($this->getSubject()->getId() != null) {
//            $file_options['help'] = '<img src="../'.$this->getFeaturedImageUrl($this->getSubject()).'">';
//        }

    $formMapper
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('file', FileType::class, $file_options)
      ->add('category', EntityType::class, [
        'class'    => MediaPackageCategory::class,
        'required' => true])
      ->add('flavor', TextType::class, ['required' => false])
      ->add('author', TextType::class, ['label' => 'Author', 'required' => false])
      ->add('active', null, ['required' => false]);
  }

  // Fields to be shown on filter forms
//    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
//    {
//        $datagridMapper
//            ->add('program', null, array('class' => 'Catrobat\AppBundle\Entity\Program', 'admin_code' => 'catrowebadmin.block.programs.all'))
//        ;
//    }

  // Fields to be shown on lists
  protected function configureListFields(ListMapper $listMapper)
  {
    $listMapper
      ->addIdentifier('id')
      ->add("name")
      ->add('file', 'string', ['template' => 'Admin/mediapackage_file.html.twig'])
      ->add('category', EntityType::class, ['class' => MediaPackageCategory::class])
      ->add('author', null, ['editable' => true])
      ->add('flavor', null, ['editable' => true])
      ->add('downloads')
      ->add('active', null, ['editable' => true])
      ->add('_action', 'actions', [
        'actions' => [
          'edit'   => [],
          'delete' => [],
        ],
      ]);
  }

  public function prePersist($object)
  {
    $file = $object->file;
    if ($file == null)
    {
      return;
    }
    $object->setExtension($file->guessExtension());
  }

  public function postPersist($object)
  {
    $file = $object->file;
    if ($file == null)
    {
      return;
    }
    $this->getConfigurationPool()->getContainer()->get('mediapackagefilerepository')->save($file, $object->getId(), $object->getExtension());
  }

  public function preUpdate($object)
  {
    $object->old_extension = $object->getExtension();
    $object->setExtension(null);

    $file = $object->file;
    if ($file == null)
    {
      $object->setExtension($object->old_extension);

      return;
    }
    $object->setExtension($file->guessExtension());
  }

  public function postUpdate($object)
  {
    $file = $object->file;
    if ($file == null)
    {
      return;
    }
    $this->getConfigurationPool()->getContainer()->get('mediapackagefilerepository')->save($file, $object->getId(), $object->getExtension());
  }

  public function preRemove($object)
  {
    $object->removed_id = $object->getId();
  }

  public function postRemove($object)
  {
    $this->getConfigurationPool()->getContainer()->get('mediapackagefilerepository')->remove($object->removed_id, $object->getExtension());
  }
}
