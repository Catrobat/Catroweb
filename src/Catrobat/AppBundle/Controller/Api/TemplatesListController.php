<?php

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Responses\TemplateListResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class TemplatesListController
 * @package Catrobat\AppBundle\Controller\Api
 */
class TemplatesListController extends Controller
{

  /**
   * @Route("/api/templates/list.json", name="api_template_list", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return TemplateListResponse
   */
  public function listTemplatesAction(Request $request)
  {
    $template_manager = $this->get('templatemanager');

    $templates = $template_manager->findAllActive();

    return new TemplateListResponse($templates);
  }
}
