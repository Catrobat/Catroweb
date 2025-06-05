<?php

declare(strict_types=1);

namespace App\Admin\System\Logs;

use App\DB\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\File;
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
    if (is_null($user) || !$user->hasRole(User::ROLE_SUPER_ADMIN)) {
      throw new AuthenticationException();
    }

    $fileName = (string) $request->request->get('file');
    $path = LogsController::LOG_DIR;
    $finder = new Finder();
    if ($finder->files()->in($path)->depth('>= 1')->name(substr($fileName, strrpos($fileName, '/') + 1))->hasResults()) {
      $file = new File($path.$fileName);
      if ($file->isFile()) {
        $response = new BinaryFileResponse($file);
        $d = $response->headers->makeDisposition(
          ResponseHeaderBag::DISPOSITION_ATTACHMENT,
          $file->getFilename()
        );
        $response->headers->set('Content-Disposition', $d);
        $response->headers->set('Content-type', 'text/plain');

        return $response;
      }
    }

    throw new NotFoundHttpException();
  }
}
