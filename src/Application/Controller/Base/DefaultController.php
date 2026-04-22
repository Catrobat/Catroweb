<?php

declare(strict_types=1);

namespace App\Application\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
}
