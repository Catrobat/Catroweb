<?php

declare(strict_types=1);

namespace App\Admin\ApkGeneration;

use App\DB\Entity\Project\Program;
use App\Storage\ScreenshotRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

/**
 * @phpstan-extends AbstractAdmin<Program>
 */
class ApkAdmin extends AbstractAdmin
{
  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'apk_request_time';
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
  }

  public function __construct(
    private readonly ScreenshotRepository $screenshot_repository,
  ) {
  }

  public function getThumbnailImageUrl(Program $object): string
  {
    return '/'.$this->screenshot_repository->getThumbnailWebPath($object->getId());
  }

  #[\Override]
  protected function configureQuery(ProxyQueryInterface $query): ProxyQueryInterface
  {
    /** @var ProxyQuery $query */
    $query = parent::configureQuery($query);
    $qb = $query->getQueryBuilder();
    $qb->andWhere(
      $qb->expr()->eq($qb->getRootAliases()[0].'.apk_status', ':apk_status')
    );

    return $query;
  }
}
