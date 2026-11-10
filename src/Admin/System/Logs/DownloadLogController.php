<?php

declare(strict_types=1);

namespace App\Admin\System\Logs;

use App\DB\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class DownloadLogController extends AbstractController
{
  #[Route(path: '/downloadLogs/', name: 'log_download')]
  public function downloadLogAction(?Request $request = null): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (is_null($user) || !$user->hasRole('ROLE_SUPER_ADMIN')) {
      throw new AuthenticationException();
    }

    if (null === $request) {
      throw new NotFoundHttpException();
    }

    if (!$this->isCsrfTokenValid('log_download', $request->request->getString('_csrf_token'))) {
      throw $this->createAccessDeniedException('Invalid CSRF token.');
    }

    $path = LogsController::resolveLogFile($request->request->getString('file'));
    if (null === $path || !is_file($path)) {
      throw new NotFoundHttpException();
    }

    $response = new BinaryFileResponse($path);
    $response->setContentDisposition(ResponseHeaderBag::DISPOSITION_ATTACHMENT, basename($path));
    $response->headers->set('Content-type', 'text/plain');

    return $response;
  }
}
