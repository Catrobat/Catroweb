<?php


namespace App\Catrobat\Controller\Admin;

use App\Catrobat\Services\TemplateService;
use App\Entity\Template;
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

  private $template_service;

  public function __construct(TemplateService $template_service)
  {
    $this->template_service = $template_service;
  }

  /**
   * @return Response
   * @throws \ImagickException
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
   * @param null $id
   *
   * @return RedirectResponse|Response
   * @throws \ImagickException
   */
  public function editAction($id = null)
  {
    $render = parent::editAction($id);
    $this->saveFiles();

    return $render;
  }


  /**
   * @throws \ImagickException
   */
  private function saveFiles()
  {
    /**
     * @var $templateService TemplateService
     * @var $template Template
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
    return $this->template_service;
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