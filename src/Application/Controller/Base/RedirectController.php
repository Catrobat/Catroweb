<?php

namespace App\Application\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RedirectController extends AbstractController
{
  /**
   * @Route("/help", name="help", methods={"GET"})
   */
  public function helpAction(Request $request): Response
  {
    $flavor = $request->attributes->get('flavor');
    if ('mindstorms' === $flavor) {
      return $this->redirect('https://catrob.at/MindstormsFlavorDocumentation');
    }

    return $this->redirect('https://wiki.catrobat.org/bin/view/Documentation/');
  }

  /**
   * @Route("/gp", name="google_play_store", methods={"GET"})
   */
  public function redirectToGooglePlayStore(Request $request): Response
  {
    $flavor = $request->attributes->get('flavor');
    if ('mindstorms' === $flavor) {
      return $this->redirect('https://catrob.at/MindstormsFlavorGooglePlay');
    }

    return $this->redirect('https://catrob.at/gp');
  }

  /**
   * @Route("/robots.txt", name="robots.txt", methods={"GET"})
   */
  public function robotsTxt(Request $request): Response
  {
    return $this->redirect('../../robots.txt', Response::HTTP_MOVED_PERMANENTLY); // The file is only hosted without flavors/themes!
  }

  /**
   * @Route("resetting/request", name="legacy_app_forgot_password_request")
   */
  public function legacyAppReset(Request $request): Response
  {
    return $this->redirect($this->generateUrl('app_forgot_password_request'), Response::HTTP_MOVED_PERMANENTLY);
  }
}
