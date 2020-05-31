<?php

namespace App\Catrobat\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class SnapshotController extends CRUDController
{
  public function listAction(Request $request = null): Response
  {
    $finder = new Finder();
    $directory = $this->getParameter('catrobat.snapshot.dir');
    $files = $finder->files()->in($directory);

    return $this->renderWithExtraParams('Admin/snapshots.html.twig', ['files' => $files]);
  }
}
