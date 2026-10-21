<?php

declare(strict_types=1);

namespace App\Api\Services;

use App\Api\MediaLibraryApi;
use App\Api\ProjectsApi;
use App\Project\ProtectedProjectFiles;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class OverwriteController extends AbstractController
{
  public function __construct(
    protected ProjectsApi $projectsApi,
    protected MediaLibraryApi $mediaLibraryApi,
    private readonly ProtectedProjectFiles $project_files,
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

    return $this->project_files->archive($id, $result)
      ?? new Response('', Response::HTTP_NOT_FOUND, ['Cache-Control' => 'private, no-store']);
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
