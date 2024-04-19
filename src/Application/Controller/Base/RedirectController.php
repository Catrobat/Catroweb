<?php

declare(strict_types=1);

namespace App\Application\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class RedirectController extends AbstractController
{
  #[Route(path: '/stepByStep', name: 'legacy_stepByStep_routed_used_by_unkwown', methods: ['GET'])]
  #[Route(path: '/help', name: 'help', methods: ['GET'])]
  public function help(Request $request): Response
  {
    $flavor = $request->attributes->get('flavor');
    if ('mindstorms' === $flavor) {
      return $this->redirect('https://catrob.at/MindstormsFlavorDocumentation');
    }

    return $this->redirect('https://wiki.catrobat.org/bin/view/Documentation/');
  }

  #[Route(path: '/gp', name: 'google_play_store', methods: ['GET'])]
  public function redirectToGooglePlayStore(Request $request): Response
  {
    $flavor = $request->attributes->get('flavor');
    if ('mindstorms' === $flavor) {
      return $this->redirect('https://catrob.at/MindstormsFlavorGooglePlay');
    }

    return $this->redirect('https://catrob.at/gp');
  }

  #[Route(path: '/robots.txt', name: 'robots.txt', methods: ['GET'])]
  public function robotsTxt(): Response
  {
    return $this->redirect('../../robots.txt', Response::HTTP_MOVED_PERMANENTLY);
    // The file is only hosted without flavors/themes!
  }

  #[Route(path: 'resetting/request', name: 'legacy_app_forgot_password_request')]
  public function legacyAppReset(): Response
  {
    return $this->redirectToRoute('app_forgot_password_request', [], Response::HTTP_MOVED_PERMANENTLY);
  }

  /**
   * Users coming from hour of code -> https://hourofcode.com/us/de/beyond.
   */
  #[Route(path: '/hourOfCode', methods: ['GET'])]
  #[Route(path: '/certificate/check', methods: ['GET'])]
  public function hourOfCode(): Response
  {
    return $this->redirect('/');
  }
}
