<?php
namespace Catrobat\AppBundle\Controller\Admin;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Finder\Finder;

class SnapshotController extends CRUDController
{
    /*
     * (non-PHPdoc)
     * @see \Sonata\AdminBundle\Controller\CRUDController::listAction()
     */
    public function listAction(Request $request = null) {
        $finder = new Finder();
        $directory = $this->container->getParameter('catrobat.snapshot.dir');
        $files = $finder->files()->in($directory);
        return $this->render(':Admin:snapshots.html.twig', array('files' => $files));
    }
}