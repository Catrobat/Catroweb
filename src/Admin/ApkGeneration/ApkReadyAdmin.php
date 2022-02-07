<?php

namespace App\Admin\ApkGeneration;

use App\DB\Entity\Project\Program;
use App\Storage\ScreenshotRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

class ApkReadyAdmin extends AbstractAdmin
{
  /**
   * @var string
   */
  protected $baseRouteName = 'admin_catrobat_apk_ready';

  /**
   * @var string
   */
  protected $baseRoutePattern = 'apk_ready';

  /**
   * @var array
   */
  protected $datagridValues = [
    '_sort_by' => 'apk_request_time',
    '_sort_order' => 'DESC',
  ];

  private ScreenshotRepository $screenshot_repository;

  /**
   * ApkReadyAdmin constructor.
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
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);

    $qb = $query->getQueryBuilder();

    $qb->andWhere(
      $qb->expr()->eq($qb->getRootAliases()[0].'.apk_status', ':apk_status')
    );
    $qb->setParameter('apk_status', Program::APK_READY);

    return $query;
  }

  /**
   * @param DatagridMapper $filter
   *
   * Fields to be shown on filter forms
   */
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('id')
      ->add('user.username', null, ['label' => 'User'])
      ->add('name')
      ->add('apk_request_time')
    ;
  }

  /**
   * @param ListMapper $list
   *
   * Fields to be shown on lists
   */
  protected function configureListFields(ListMapper $list): void
  {
    $list
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
    $collection->add('requestApkRebuild', $this->getRouterIdParameter().'/requestApkRebuild');
    $collection->add('resetApkBuildStatus', $this->getRouterIdParameter().'/resetApkBuildStatus');
    $collection->add('rebuildAllApk');
    $collection->add('resetPendingProjects');
  }
}
