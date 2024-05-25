<?php

declare(strict_types=1);

namespace App\Admin\MediaPackage;

use App\DB\Entity\MediaLibrary\MediaPackageCategory;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @phpstan-extends CRUDController<MediaPackageCategory>
 */
class MediaPackageCategoryController extends CRUDController
{
  #[\Override]
  protected function preDelete(Request $request, object $object): ?Response
  {
    /* @var $object MediaPackageCategory */
    if ($object->getFiles()->count() > 0) {
      $this->addFlash('sonata_flash_error', 'This category is used by media package files!');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    return null;
  }
}
