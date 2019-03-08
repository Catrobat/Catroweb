<?php


namespace App\Catrobat\Controller\Admin;

use App\Catrobat\Services\TemplateService;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;


/**
 * Class TemplateController
 * @package App\Catrobat\Controller\Admin
 */
class TemplateController extends CRUDController
{

  /**
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function createAction()
  {
    $response = parent::createAction();
    $this->saveFiles();

    return $response;
  }


  /**
   * @param int|null $id
   *
   * @return RedirectResponse|Response
   */
  public function deleteAction($id)
  {
    $templateService = $this->getTemplateService();
    $templateService->deleteTemplateFiles($id);

    return parent::deleteAction($id);
  }


  /**
   * @param int|null $id
   *
   * @return RedirectResponse|Response
   */
  public function editAction($id = null)
  {
    $render = parent::editAction($id);
    $this->saveFiles();

    return $render;
  }


  /**
   *
   */
  private function saveFiles()
  {
    /**
     * @var $templateService TemplateService
     * @var $template \App\Entity\Template
     */
    $template = $this->getTemplate();
    if ($template->getId() != null)
    {
      $templateService = $this->getTemplateService();
      $templateService->saveFiles($template);
    }
  }


  /**
   * @return TemplateService|object
   */
  private function getTemplateService()
  {
    return $this->get("template_service");
  }


  /**
   * @return mixed
   */
  private function getTemplate()
  {
    $object = $this->admin->getSubject();
    if (!$object)
    {
      throw new NotFoundHttpException(sprintf('unable to find the object'));
    }

    return $object;
  }
}