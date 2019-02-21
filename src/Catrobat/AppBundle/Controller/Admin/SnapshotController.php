<?php

namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;


/**
 * Class SnapshotController
 * @package Catrobat\AppBundle\Controller\Admin
 */
class SnapshotController extends CRUDController
{

  /**
   * @param Request|null $request
   *
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