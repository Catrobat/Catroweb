<?php

declare(strict_types=1);

namespace App\Admin\Projects\UploadProject;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Route\RouteCollectionInterface;

/**
 * @phpstan-extends AbstractAdmin<\stdClass>
 */
class UploadProjectAdmin extends AbstractAdmin
{
  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_upload_project';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'projects/upload';
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->clearExcept(['list']);
    $collection->add('upload');
  }
}
