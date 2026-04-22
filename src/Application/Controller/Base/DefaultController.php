<?php

declare(strict_types=1);

namespace App\Application\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class DefaultController extends AbstractController
{
  #[Route(path: '/termsOfUse', name: 'termsOfUse', methods: ['GET'])]
  public function termsOfUse(): Response
  {
    return $this->render('PrivacyAndTerms/TermsOfUsePage.html.twig');
  }

  #[Route(path: '/privacypolicy', name: 'privacypolicy', methods: ['GET'])]
  public function privacyPolicy(): Response
  {
    return $this->redirect('https://developer.catrobat.org/pages/legal/policies/privacy/');
  }

  #[Route(path: '/licenseToPlay', name: 'licenseToPlay', methods: ['GET'])]
  public function licenseToPlay(): Response
  {
    return $this->render('PrivacyAndTerms/LicenseToPlayPage.html.twig');
  }

  /**
   * @deprecated Use GET /api/languages instead. Will be removed in a future release.
   */
  #[Route(path: '/languages', name: 'languages', methods: ['GET'])]
  public function languages(): Response
  {
    return new RedirectResponse(
      $this->generateUrl('open_api_server_utility_languagesget', [], UrlGeneratorInterface::ABSOLUTE_URL),
      Response::HTTP_MOVED_PERMANENTLY,
    );
  }
}
