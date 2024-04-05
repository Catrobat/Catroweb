<?php

declare(strict_types=1);

namespace App\Api_deprecated\Controller;

use App\Api\ProjectsApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class DownloadProjectController extends AbstractController
{
  public function __construct(protected ProjectsApi $projectsApi, protected LoggerInterface $logger)
  {
  }

  /**
   * @deprecated
   */
  #[Route(path: '/download/{id}.catrobat', name: 'legacy_download_route_deprecated', methods: ['GET'])]
  public function downloadProject(string $id): ?Response
  {
    $this->logger->warning("Deprecated 'download catrobat project file' route was used!");
    $responseCode = 200;
    $responseHeaders = [];
    $result = $this->projectsApi->customProjectIdCatrobatGet($id, $responseCode, $responseHeaders);
    if (null === $result) {
      return new Response(null, $responseCode, $responseHeaders);
    }

    return $result;
  }
}
