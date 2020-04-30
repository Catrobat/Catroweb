<?php

namespace App\Admin;

use App\Catrobat\Services\MediaPackageFileRepository;
use App\Entity\MediaPackageCategory;
use App\Entity\MediaPackageFile;
use ImagickException;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MediaPackageFileAdmin extends AbstractAdmin
{
  /**
   * @override
   *
   * @var string
   */
  protected $baseRouteName = 'adminmedia_package_file';

  /**
   * @override
   *
   * @var string
   */
  protected $baseRoutePattern = 'media_package_file';

  private MediaPackageFileRepository $media_package_file_repository;

  private ParameterBagInterface $parameter_bag;

  public function __construct($code, $class, $baseControllerName,
                              MediaPackageFileRepository $media_package_file_repository,
                              ParameterBagInterface $parameter_bag)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->media_package_file_repository = $media_package_file_repository;
    $this->parameter_bag = $parameter_bag;
  }

  /**
   * @param MediaPackageFile $object
   */
  public function prePersist($object): void
  {
    /** @var UploadedFile $file */
    $file = $object->file;
    if (null == $file)
    {
      return;
    }

    $object->setExtension(('catrobat' == $file->getClientOriginalExtension()) ? 'catrobat' : $file->guessExtension());

    $this->checkFlavor();
  }

  /**
   * @param MediaPackageFile $object
   *
   * @throws ImagickException
   */
  public function postPersist($object): void
  {
    $file = $object->file;
    if (null === $file)
    {
      return;
    }
    $this->media_package_file_repository->moveFile($file, $object->getId(), $object->getExtension());
  }

  /**
   * @param MediaPackageFile $object
   */
  public function preUpdate($object): void
  {
    $object->old_extension = $object->getExtension();

    /** @var UploadedFile $file */
    $file = $object->file;
    if (null == $file)
    {
      $object->setExtension($object->old_extension);

      return;
    }
    $object->setExtension(('catrobat' == $file->getClientOriginalExtension()) ? 'catrobat' : $file->guessExtension());
    $this->checkFlavor();
  }

  /**
   * @param MediaPackageFile $object
   *
   * @throws ImagickException
   */
  public function postUpdate($object): void
  {
    $file = $object->file;
    if (null === $file)
    {
      return;
    }
    $this->media_package_file_repository->moveFile($file, $object->getId(), $object->getExtension());
  }

  /**
   * @param MediaPackageFile $object
   */
  public function preRemove($object): void
  {
    $object->removed_id = $object->getId();
  }

  /**
   * @param MediaPackageFile $object
   */
  public function postRemove($object): void
  {
    $this->media_package_file_repository->remove($object->removed_id, $object->getExtension());
  }

  /**
   * @param FormMapper $formMapper
   *
   * Fields to be shown on create/edit forms
   */
  protected function configureFormFields(FormMapper $formMapper): void
  {
    $file_options = [
      'required' => (null === $this->getSubject()->getId()), ];

    $formMapper
      ->add('name', TextType::class, ['label' => 'Name'])
      ->add('file', FileType::class, $file_options)
      ->add('category', EntityType::class, [
        'class' => MediaPackageCategory::class,
        'required' => true, ])
      ->add('flavor', TextType::class, ['required' => true])
      ->add('author', TextType::class, ['label' => 'Author', 'required' => false])
      ->add('active', null, ['required' => false])
    ;
  }

  /**
   * @param ListMapper $listMapper
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $listMapper): void
  {
    $listMapper
      ->addIdentifier('id')
      ->add('name')
      ->add('file', 'string', ['template' => 'Admin/mediapackage_file.html.twig'])
      ->add('category', EntityType::class, ['class' => MediaPackageCategory::class])
      ->add('author', null, ['editable' => true])
      ->add('flavor', null, ['editable' => true])
      ->add('downloads')
      ->add('active', null, ['editable' => true])
      ->add('_action', 'actions', [
        'actions' => [
          'edit' => [],
          'delete' => [],
        ],
      ])
    ;
  }

  private function checkFlavor(): void
  {
    $flavor = $this->getForm()->get('flavor')->getData();

    if (!$flavor)
    {
      return; // There was no required flavor form field in this Action, so no check is needed!
    }

    $flavor_options = $this->parameter_bag->get('themes');

    if (!in_array($flavor, $flavor_options, true))
    {
      throw new NotFoundHttpException('"'.$flavor.'"Flavor is unknown! Choose either '.implode(',', $flavor_options));
    }
  }
}
