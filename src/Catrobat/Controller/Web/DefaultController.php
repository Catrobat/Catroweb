<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\ImageRepository;
use App\Entity\FeaturedProgram;
use App\Entity\User;
use App\Repository\FeaturedRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Intl\Locales;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
  /**
   * @Route("/", name="index", methods={"GET"})
   */
  public function indexAction(Request $request, ImageRepository $image_repository, FeaturedRepository $repository): Response
  {
    $flavor = $request->attributes->get('flavor');

    if ('phirocode' === $flavor) {
      $featured_items = $repository->getFeaturedItems('pocketcode', 10, 0);
    } else {
      $featured_items = $repository->getFeaturedItems($flavor, 10, 0);
    }

    $featured = [];
    foreach ($featured_items as $item) {
      /** @var FeaturedProgram $item */
      $info = [];
      if (null !== $item->getProgram()) {
        if ($flavor) {
          $info['url'] = $this->generateUrl('program',
          ['id' => $item->getProgram()->getId(), 'theme' => $flavor]);
        } else {
          $info['url'] = $this->generateUrl('program', ['id' => $item->getProgram()->getId()]);
        }
      } else {
        $info['url'] = $item->getUrl();
      }
      $info['image'] = $image_repository->getWebPath($item->getId(), $item->getImageType(), true);

      $featured[] = $info;
    }

    return $this->render('Index/index.html.twig', [
      'featured' => $featured,
    ]);
  }

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
   * @Route("/termsOfUse", name="termsOfUse", methods={"GET"})
   */
  public function termsOfUseAction(): Response
  {
    return $this->render('PrivacyAndTerms/termsOfUse.html.twig');
  }

  /**
   * @Route("/privacypolicy", name="privacypolicy", methods={"GET"})
   */
  public function privacyPolicyAction(): Response
  {
    return $this->redirect('https://catrob.at/privacypolicy');
  }

  /**
   * @Route("/licenseToPlay", name="licenseToPlay", methods={"GET"})
   */
  public function licenseToPlayAction(): Response
  {
    return $this->render('PrivacyAndTerms/licenseToPlay.html.twig');
  }

  /**
   * @Route("/checkFirstOauthLogin", name="oauth_first_login", methods={"GET"})
   */
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

    return JsonResponse::create([
      'first_login' => $user_first_login,
      'user_id' => $user_id,
    ]);
  }

  /**
   * @Route("/languages", name="languages", methods={"GET"})
   */
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

    $all_locales = array_filter($all_locales, function ($key) {
      return 2 == strlen($key) || 5 == strlen($key);
    }, ARRAY_FILTER_USE_KEY);

    $locales = [];
    foreach ($all_locales as $key => $value) {
      $locales[str_replace('_', '-', $key)] = $value;
    }

    return $response->setData($locales);
  }
}
