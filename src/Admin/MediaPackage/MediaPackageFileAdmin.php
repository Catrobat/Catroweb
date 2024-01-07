<?php

namespace App\Admin\MediaPackage;

use App\DB\Entity\Flavor;
use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use App\DB\Entity\MediaLibrary\MediaPackageFile;
use App\DB\EntityRepository\MediaLibrary\MediaPackageFileRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * @phpstan-extends AbstractAdmin<MediaPackageFile>
 */
class MediaPackageFileAdmin extends AbstractAdmin
{
  protected $baseRouteName = 'adminmedia_package_file';

  protected $baseRoutePattern = 'media_package_file';

  public function __construct(
    private readonly MediaPackageFileRepository $media_package_file_repository
  ) {
  }

  public function prePersist($object): void
  {
    /* @var MediaPackageFile $object */
    /** @var UploadedFile $file */
    $file = $object->file;
    if (null == $file) {
      return;
    }

    $object->setExtension(('catrobat' == $file->getClientOriginalExtension()) ? 'catrobat' : $file->guessExtension());
  }

  /**
   * @throws \ImagickException
   */
  public function postPersist($object): void
  {
    /* @var MediaPackageFile $object */
    $file = $object->file;
    if (null === $file) {
      return;
    }
    $this->media_package_file_repository->moveFile($file, $object->getId(), $object->getExtension());
  }

  public function preUpdate(object $object): void
  {
    /* @var MediaPackageFile $object */
    $object->old_extension = $object->getExtension();

    /** @var UploadedFile $file */
    $file = $object->file;
    if (null == $file) {
      $object->setExtension($object->old_extension);

      return;
    }
    $object->setExtension(('catrobat' == $file->getClientOriginalExtension()) ? 'catrobat' : $file->guessExtension());
  }

  /**
   * @throws \ImagickException
   */
  public function postUpdate($object): void
  {
    /* @var MediaPackageFile $object */
    $file = $object->file;
    if (null === $file) {
      return;
    }
    $this->media_package_file_repository->moveFile($file, $object->getId(), $object->getExtension());
  }

  public function preRemove($object): void
  {
    /* @var MediaPackageFile $object */
    $object->removed_id = $object->getId();
  }

  public function postRemove($object): void
  {
    /* @var MediaPackageFile $object */
    $this->media_package_file_repository->remove($object->removed_id, $object->getExtension());
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $form): void
  {
    $file_options = [
      'required' => (null === $this->getSubject()->getId()), ];

    $form
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('file', FileType::class, $file_options)
      ->add('category', EntityType::class, [
        'class' => MediaPackageCategory::class,
        'required' => true, ])
      ->add('flavors', null, ['class' => Flavor::class, 'multiple' => true, 'required' => true])
      ->add('author', TextType::class, ['label' => 'Author', 'required' => false])
      ->add('active', null, ['required' => false])
      ->add('url', TextType::class, ['required' => false, 'label' => 'Project ID'])
    ;
  }

  /**
   * {@inheritdoc}
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->addIdentifier('id')
      ->add('name')
      ->add('file', 'string', ['template' => 'Admin/mediapackage_file.html.twig'])
      ->add('category', EntityType::class, ['class' => MediaPackageCategory::class])
      ->add('author', null, ['editable' => true])
      ->add('flavors', null, ['multiple' => true])
      ->add('downloads')
      ->add('active', null, ['editable' => true])
      ->add(ListMapper::NAME_ACTIONS, null, [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }
}
