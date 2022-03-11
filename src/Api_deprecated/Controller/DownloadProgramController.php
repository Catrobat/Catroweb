<?php

namespace App\Api_deprecated\Controller;

use App\Api\ProjectsApi;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @deprecated
 */
class DownloadProgramController extends AbstractController
{
  protected LoggerInterface $logger;
  protected ProjectsApi $projectsApi;

  public function __construct(ProjectsApi $projectsApi, LoggerInterface $logger)
  {
    $this->logger = $logger;
    $this->projectsApi = $projectsApi;
  }

  /**
   * @deprecated
   * @Route("/download/{id}.catrobat", name="legacy_download_route_deprecated", methods={"GET"})
   */
  public function downloadProgramAction(string $id)
  {
    $this->logger->warning("Deprecated 'download catrobat project file' route was used!");

    $responseCode = 200;
    $responseHeaders = [];
    $result = $this->projectsApi->customProjectIdCatrobatGet($id, $responseCode, $responseHeaders);

    if (200 !== $responseCode) {
      return new Response(null, $responseCode, $responseHeaders);
    }

    return $result;
  }
}
