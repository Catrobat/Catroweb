<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\Responses\TemplateListResponse;
use App\Entity\TemplateManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class TemplatesListController
 * @package App\Catrobat\Controller\Api
 */
class TemplatesListController extends AbstractController
{

  /**
   * @Route("/api/templates/list.json", name="api_template_list", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return TemplateListResponse
   */
  public function listTemplatesAction(Request $request, TemplateManager $template_manager)
  {
    $templates = $template_manager->findAllActive();

    return new TemplateListResponse($templates);
  }
}
