<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\MediaPackageCategory;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

class MediaPackageCategoryController extends CRUDController
{
  protected function preDelete(Request $request, $object)
  {
    /* @var $object MediaPackageCategory */
    if ($object->getFiles()->count() > 0)
    {
      $this->addFlash('sonata_flash_error', 'This category is used by media package files!');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    return null;
  }
}
