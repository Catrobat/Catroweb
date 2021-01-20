<?php

namespace App\Admin;

use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\Program;
use Doctrine\ORM\QueryBuilder;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class ApkListAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_apk_list';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'apk_list';

  /**
   * @var array
   */
  protected $datagridValues = [
    '_sort_by' => 'apk_request_time',
    '_sort_order' => 'DESC',
  ];

  private ScreenshotRepository $screenshot_repository;

  /**
   * ApkListAdmin constructor.
   *
   * @param mixed $code
   * @param mixed $class
   * @param mixed $baseControllerName
   */
  public function __construct($code, $class, $baseControllerName, ScreenshotRepository $screenshot_repository)
  {
    parent::__construct($code, $class, $baseControllerName);
    $this->screenshot_repository = $screenshot_repository;
  }

  public function getThumbnailImageUrl(Program $object): string
  {
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
  }

  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    $query = parent::configureQuery($query);

    if (!$query instanceof ProxyQuery)
    {
      return $query;
    }

    /** @var QueryBuilder $qb */
    $qb = $query->getQueryBuilder();

    $qb->andWhere(
      $qb->expr()->eq($qb->getRootAliases()[0].'.apk_status', ':apk_status')
    );
    $qb->setParameter('apk_status', Program::APK_READY);

    return $query;
  }

  /**
   * @param DatagridMapper $datagridMapper
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
  {
    $datagridMapper
      ->add('id')
      ->add('user.username', null, ['label' => 'User'])
      ->add('name')
      ->add('apk_request_time')
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
      ->add('user', null, [
        'route' => [
          'name' => 'show',
        ],
      ])
      ->add('name')
      ->add('apk_request_time')
      ->add('thumbnail', 'string', ['template' => 'Admin/program_thumbnail_image_list.html.twig'])
      ->add('_action', 'actions', [
        'actions' => [
          'Reset' => [
            'template' => 'Admin/CRUD/list__action_reset_status.html.twig',
          ],
          'Rebuild' => [
            'template' => 'Admin/CRUD/list__action_rebuild_apk.html.twig',
          ],
        ],
      ])
    ;
  }

  protected function configureRoutes(RouteCollection $collection): void
  {
    $collection->clearExcept(['list']);
    $collection->add('rebuildApk', $this->getRouterIdParameter().'/rebuildApk');
    $collection->add('resetStatus', $this->getRouterIdParameter().'/resetStatus');
    $collection->add('rebuildAllApk');
    $collection->add('resetAllApk');
  }
}
