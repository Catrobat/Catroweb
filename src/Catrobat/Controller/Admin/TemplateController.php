<?php

namespace App\Catrobat\Controller\Admin;

use App\Catrobat\Services\TemplateService;
use App\Entity\Template;
use ImagickException;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TemplateController extends CRUDController
{
  private TemplateService $template_service;

  public function __construct(TemplateService $template_service)
  {
    $this->template_service = $template_service;
  }

  /**
   * @throws ImagickException
   */
  public function createAction(): Response
  {
    $response = parent::createAction();
    $this->saveFiles();

    return $response;
  }

  /**
   * @param int|null $id
   */
  public function deleteAction($id): Response
  {
    $templateService = $this->getTemplateService();
    $templateService->deleteTemplateFiles($id);

    return parent::deleteAction($id);
  }

  /**
   * @param null $id
   *
   * @throws ImagickException
   */
  public function editAction($id = null): Response
  {
    $render = parent::editAction($id);
    $this->saveFiles();

    return $render;
  }

  /**
   * @throws ImagickException
   */
  private function saveFiles(): void
  {
    $template = $this->getTemplate();
    if (null != $template->getId())
    {
      $templateService = $this->getTemplateService();
      $templateService->saveFiles($template);
    }
  }

  private function getTemplateService(): TemplateService
  {
    return $this->template_service;
  }

  private function getTemplate(): Template
  {
    /** @var Template|null $object */
    $object = $this->admin->getSubject();
    if (null === $object)
    {
      throw new NotFoundHttpException(sprintf('unable to find the object'));
    }

    return $object;
  }
}
