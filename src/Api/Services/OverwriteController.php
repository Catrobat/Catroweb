<?php

namespace App\Api\Services;

use App\Api\ProjectsApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class OverwriteController extends AbstractController
{
  public function __construct(protected ProjectsApi $projectsApi)
  {
  }

  public function projectIdCatrobatGetAction(string $id): ?Response
  {
    $responseCode = 200;
    $responseHeaders = [];
    $result = $this->projectsApi->customProjectIdCatrobatGet($id, $responseCode, $responseHeaders);

    if (null === $result) {
      return new Response(null, $responseCode, $responseHeaders);
    }

    return $result;
  }
}
