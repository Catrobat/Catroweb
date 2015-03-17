<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\StatusCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Catrobat\AppBundle\Services\FeaturedImageRepository;
use Catrobat\AppBundle\Entity\FeaturedRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class DefaultController extends Controller
{
  const MIN_PASSWORD_LENGTH = 6;
  const MAX_PASSWORD_LENGTH = 32;

  public function headerAction()
  {
    return $this->get("templating")->renderResponse(':Default:header.html.twig');
  }

  public function headerLogoAction()
  {
    return $this->get("templating")->renderResponse(':Default:headerLogo.html.twig');
  }

  public function footerAction()
  {
    return $this->get("templating")->renderResponse(':Default:footer.html.twig');
  }

  /**
   * @Route("/", name="catrobat_web_index")
   * @Method({"GET"})
   */
  public function indexAction()
  {
      /* @var $image_repository FeaturedImageRepository */
      $image_repository = $this->get('featuredimagerepository');
      /* @var $repository FeaturedRepository */
      $repository = $this->get('featuredrepository');

      $programs = $repository->getFeaturedPrograms(5, 0);

      $featured = array();
      foreach ($programs as $program)
      {
          $info = array();
          if ($program->getProgram() !== null)
          {
              $info['url'] = $this->generateUrl('catrobat_web_program', array('id' => $program->getProgram()->getId()));
          }
          else 
          {
              $info['url'] = $program->getUrl();
          }
          $info['image'] = $image_repository->getWebPath($program->getId(), $program->getImageType());;
          $featured[] = $info;
      }
    return $this->get("templating")->renderResponse('::index.html.twig', array("featured" => $featured));
  }

  /**
   * @Route("/program/{id}", name="catrobat_web_program", requirements={"id":"\d+"})
   * @Route("/details/{id}", name="catrobat_web_detail", requirements={"id":"\d+"})
   * @Method({"GET"})
   */
  public function programAction($id)
  {
    //IMPORTANT: if you change the route '/program' .. also adapt it in ProgramLoader.js (variable: 'program_link')

    $program = $this->get("programmanager")->find($id);

    if (!$program) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }
    return $this->get("templating")->renderResponse('::program.html.twig', array("program" => $program));
  }

  /**
   * @Route("/search/{q}", name="catrobat_web_search", requirements={"q":".+"})
   * @Method({"GET"})
   */
  public function searchAction($q)
  {
    return $this->get("templating")->renderResponse('::search.html.twig', array("q" => $q));
  }

  /**
   * @Route("/search/", name="catrobat_web_search_nothing")
   * @Method({"GET"})
   */
  public function searchNothingAction()
  {
    return $this->get("templating")->renderResponse('::search.html.twig', array("q" => null));
  }

  /**
   * @Route("/profile/{id}", name="catrobat_web_profile", requirements={"id":"\d+"}, defaults={"id" = 0})
   * @Method({"GET"})
   */
  public function profileAction($id)
  {
    $twig = '::profile.html.twig';

    if($id == 0) {
      $user = $this->getUser();
      $twig = '::myprofile.html.twig';
    }
    else
      $user = $this->get("usermanager")->find($id);

    if (!$user)
      throw $this->createNotFoundException('Unable to find User entity.');

    $profile = $user->getId();

    return $this->get("templating")->renderResponse($twig, array(
      "profile" => $profile,
      "minPassLength" => self::MIN_PASSWORD_LENGTH,
      "maxPassLength" => self::MAX_PASSWORD_LENGTH
    ));
  }

  /**
   * @Route("/profileSave", name="catrobat_web_profile_save")
   * @Method({"POST"})
   */
  public function profileSaveAction(Request $request)
  {
    $user = $this->getUser();
    if(!$user)
      return JsonResponse::create(array('statusCode' => StatusCode::INTERNAL_SERVER_ERROR));

    $newPassword = $request->request->get('newPassword');
    $repeatPassword = $request->request->get('repeatPassword');
    $firstMail = $request->request->get('firstEmail');
    $secondMail = $request->request->get('secondEmail');
    $country = $request->request->get('country');

    try {
      $this->validateUserPassword($newPassword, $repeatPassword);
      $this->validateEmail($firstMail);
      $this->validateEmail($secondMail);
      $this->validateCountryCode($country);
    } catch(\Exception $e) {
      return JsonResponse::create(array('statusCode' => $e->getMessage()));
    }

    if($firstMail === "" && $secondMail === "")
      return JsonResponse::create(array('statusCode' => StatusCode::USER_UPDATE_EMAIL_FAILED));

    if($this->checkEmailExists($firstMail))
      return JsonResponse::create(array('statusCode' => StatusCode::EMAIL_ALREADY_EXISTS, 'email' => 1));
    if($this->checkEmailExists($secondMail))
      return JsonResponse::create(array('statusCode' => StatusCode::EMAIL_ALREADY_EXISTS, 'email' => 2));

    if($newPassword !== "")
      $user->setPlainPassword($newPassword);
    if($firstMail !== "" && $firstMail != $user->getEmail())
      $user->setEmail($firstMail);
    if($firstMail !== "" && $secondMail !== "" && $secondMail != $user->getAdditionalEmail())
      $user->setAdditionalEmail($secondMail);
    if($firstMail === "" && $user->getAdditionalEmail() !== "") {
      $user->setEmail($user->getAdditionalEmail());
      $user->setAdditionalEmail("");
    }

    $user->setCountry($country);

    $this->get("usermanager")->updateUser($user);

    return JsonResponse::create(array('statusCode' => StatusCode::OK));
  }

  /**
   * @Route("/termsOfUse", name="catrobat_web_termsOfUse")
   * @Method({"GET"})
   */
  public function termsOfUseAction()
  {
    return $this->get("templating")->renderResponse('::termsOfUse.html.twig');
  }

  /**
   * @Route("/licenseToPlay", name="catrobat_web_licenseToPlay")
   * @Method({"GET"})
   */
  public function licenseToPlayAction()
  {
    return $this->get("templating")->renderResponse('::licenseToPlay.html.twig');
  }


  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //// private functions
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  /*
   * @param string $username
   * @param int $pass1
   * @param int $pass2
   */
  private function validateUserPassword($pass1, $pass2)
  {
    if($pass1 !== $pass2)
      throw new \Exception(StatusCode::USER_PASSWORD_NOT_EQUAL_PASSWORD2);

    if(strcasecmp($this->getUser()->getUsername(), $pass1) == 0)
      throw new \Exception(StatusCode::USER_USERNAME_PASSWORD_EQUAL);

    if($pass1 != "" && strlen($pass1) < self::MIN_PASSWORD_LENGTH)
      throw new \Exception(StatusCode::USER_PASSWORD_TOO_SHORT);

    if($pass1 != "" && strlen($pass1) > self::MAX_PASSWORD_LENGTH)
      throw new \Exception(StatusCode::USER_PASSWORD_TOO_LONG);
  }

  /*
   * @param string $email
   */
  private function validateEmail($email)
  {
    $name = '[a-zA-Z0-9]((\.|\-|_)?[a-zA-Z0-9])*';
    $domain = '[a-zA-Z]((\.|\-)?[a-zA-Z0-9])*';
    $tld = '[a-zA-Z]{2,8}';
    $regEx = '/^('.$name.')@('.$domain.')\.('.$tld.')$/';

    if(!preg_match($regEx, $email) && !empty($email))
      throw new \Exception(StatusCode::USER_EMAIL_INVALID);
  }

  /*
   * @param string $country
   */
  private function validateCountryCode($country)
  {
    //todo: check if code is really from the drop-down
    if(!empty($country) && !preg_match('/[a-zA-Z]{2}/', $country))
      throw new \Exception(StatusCode::USER_COUNTRY_INVALID);
  }

  /*
   * @param string $email
   * @return bool
   */
  private function checkEmailExists($email)
  {
    $userWithFirstMail = $this->get("usermanager")->findOneBy(array('email' => $email));
    $userWithSecondMail = $this->get("usermanager")->findOneBy(array('additional_email' => $email));

    if($userWithFirstMail != null && $userWithFirstMail != $this->getUser() || $userWithSecondMail != null && $userWithSecondMail != $this->getUser())
      return true;
    return false;
  }
}
