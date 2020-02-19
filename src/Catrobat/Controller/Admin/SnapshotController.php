<?php

namespace App\Catrobat\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SnapshotController.
 */
class SnapshotController extends CRUDController
{
  /**
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function listAction(Request $request = null)
  {
    $finder = new Finder();
    $directory = $this->container->getParameter('catrobat.snapshot.dir');
    $files = $finder->files()->in($directory);

    return $this->renderWithExtraParams('Admin/snapshots.html.twig', ['files' => $files]);
  }
}
