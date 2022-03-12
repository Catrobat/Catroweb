<?php

namespace App\Api\Services;

use App\Api\ProjectsApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OverwriteController extends AbstractController
{
  protected ProjectsApi $projectsApi;

  public function __construct(ProjectsApi $projectsApi)
  {
    $this->projectsApi = $projectsApi;
  }

  public function projectIdCatrobatGetAction(string $id)
  {
    $responseCode = 200;
    $responseHeaders = [];
    $result = $this->projectsApi->customProjectIdCatrobatGet($id, $responseCode, $responseHeaders);

    if (200 !== $responseCode) {
      return new Response(null, $responseCode, $responseHeaders);
    }

    return $result;
  }
}
