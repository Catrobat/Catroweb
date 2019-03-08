<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\Program;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class ApproveProgramController
 * @package App\Catrobat\Controller\Admin
 */
class ApproveProgramController extends Controller
{

  /**
   * @return RedirectResponse
   */
  public function approveAction()
  {
    /**
     * @var $object Program
     */
    $object = $this->admin->getSubject();
    if (!$object)
    {
      throw new NotFoundHttpException(sprintf('unable to find the object'));
    }
    $object->setApproved(true);
    $object->setVisible(true);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_success', $object->getName() . ' approved. ' . $this->getRemainingProgramCount() . ' remaining.');

    return new RedirectResponse($this->getRedirectionUrl());
  }


  /**
   * @return RedirectResponse
   */
  public function skipAction()
  {
    $object = $this->admin->getSubject();
    if (!$object)
    {
      throw new NotFoundHttpException(sprintf('unable to find the object'));
    }
    $this->addFlash('sonata_flash_warning', $object->getName() . ' skipped');

    return new RedirectResponse($this->getRedirectionUrl());
  }


  /**
   * @return RedirectResponse
   */
  public function invisibleAction()
  {
    /**
     * @var $object Program
     */

    $object = $this->admin->getSubject();
    if (!$object)
    {
      throw new NotFoundHttpException(sprintf('unable to find the object'));
    }
    $object->setApproved(true);
    $object->setVisible(false);
    $this->admin->update($object);

    $this->addFlash('sonata_flash_success', $object->getName() . ' set to invisible' . $this->getRemainingProgramCount() . ' remaining.');

    return new RedirectResponse($this->getRedirectionUrl());
  }


  /**
   * @return string
   */
  private function getRedirectionUrl()
  {
    $nextId = $this->getNextRandomApproveProgramId();
    if ($nextId == null)
    {
      return $this->admin->generateUrl('list');
    }

    return $this->admin->generateUrl('show', ['id' => $nextId]);
  }


  /**
   * @return Program|null
   */
  private function getNextRandomApproveProgramId()
  {
    /**
     * @var $object Program
     */

    $datagrid = $this->admin->getDatagrid();

    $objects = $datagrid->getResults();
    if (count($objects) == 0)
    {
      return null;
    }
    $object_key = array_rand($objects);

    $object = $objects[$object_key];
    return $object->getId();
  }

  /**
   * @return int
   */
  private function getRemainingProgramCount()
  {
    $datagrid = $this->admin->getDatagrid();
    $objects = $datagrid->getResults();

    return count($objects);
  }
}
