<?php

declare(strict_types=1);

namespace App\Api\Services;

use App\Api\MediaLibraryApi;
use App\Api\ProjectsApi;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class OverwriteController extends AbstractController
{
  public function __construct(
    protected ProjectsApi $projectsApi,
    protected MediaLibraryApi $mediaLibraryApi,
  ) {
  }

  public function projectsIdCatrobatGet(string $id): ?Response
  {
    $responseCode = 200;
    $responseHeaders = [];
    $result = $this->projectsApi->customProjectsIdCatrobatGet($id, $responseCode, $responseHeaders);

    if (!$result instanceof BinaryFileResponse) {
      return new Response(null, $responseCode, $responseHeaders);
    }

    return $result;
  }

  public function mediaAssetsIdDownloadGet(string $id): Response
  {
    $responseCode = 200;
    $responseHeaders = [];
    $result = $this->mediaLibraryApi->mediaAssetsIdDownloadGet($id, $responseCode, $responseHeaders);

    if (!$result instanceof BinaryFileResponse) {
      return new Response(null, $responseCode, $responseHeaders);
    }

    return $result;
  }
}
