<?php

namespace App\Application\Controller\Base;

use App\DB\Entity\User\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
  #[Route(path: '/termsOfUse', name: 'termsOfUse', methods: ['GET'])]
  public function termsOfUseAction(): Response
  {
    return $this->render('PrivacyAndTerms/termsOfUse.html.twig');
  }

  #[Route(path: '/privacypolicy', name: 'privacypolicy', methods: ['GET'])]
  public function privacyPolicyAction(): Response
  {
    return $this->redirect('https://catrob.at/privacypolicy');
  }

  #[Route(path: '/licenseToPlay', name: 'licenseToPlay', methods: ['GET'])]
  public function licenseToPlayAction(): Response
  {
    return $this->render('PrivacyAndTerms/licenseToPlay.html.twig');
  }

  #[Route(path: '/checkFirstOauthLogin', name: 'oauth_first_login', methods: ['GET'])]
  public function checkOauthFirstLogin(): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    $user_first_login = false;
    $user_id = null;
    if (null !== $user && true == $user->isOauthUser() && !$user->isOauthPasswordCreated()) {
      $user_first_login = true;
      $user_id = $user->getId();
    }

    return new JsonResponse([
      'first_login' => $user_first_login,
      'user_id' => $user_id,
    ]);
  }

  #[Route(path: '/languages', name: 'languages', methods: ['GET'])]
  public function languagesAction(Request $request): Response
  {
    $display_locale = $request->getLocale();
    $response = new JsonResponse();
    $response->setEtag($display_locale);
    $response->setPublic();
    if ($response->isNotModified($request)) {
      return $response;
    }
    $all_locales = Locales::getNames($display_locale);
    $all_locales = array_filter($all_locales, fn ($key) => 2 == strlen((string) $key) || 5 == strlen((string) $key), ARRAY_FILTER_USE_KEY);
    $locales = [];
    foreach ($all_locales as $key => $value) {
      $locales[str_replace('_', '-', (string) $key)] = $value;
    }

    return $response->setData($locales);
  }
}
