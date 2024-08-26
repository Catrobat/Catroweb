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
    $locale = $request->getLocale();
    if (str_contains($locale, 'de')) {
      return $this->redirect('https://catrobat.org/de/dokumentation/');
    }

    return $this->redirect('https://catrobat.org/docs/');
  }

  #[Route(path: '/gp', name: 'google_play_store', methods: ['GET'])]
  public function redirectToGooglePlayStore(): Response
  {
    return $this->redirect('https://play.google.com/store/apps/developer?id=Catrobat');
  }

  #[Route(path: '/as', name: 'apple_app_store', methods: ['GET'])]
  public function redirectToAppleAppStore(): Response
  {
    return $this->redirect('https://apps.apple.com/at/developer/international-catrobat-association-verein-zur-foerderung/id1117935891');
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
