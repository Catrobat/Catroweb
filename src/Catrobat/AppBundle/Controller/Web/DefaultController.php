<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\StatusCode;
use Catrobat\AppBundle\Entity\User;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Catrobat\AppBundle\Services\FeaturedImageRepository;
use Catrobat\AppBundle\Entity\FeaturedRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;

class DefaultController extends Controller
{
  const MIN_PASSWORD_LENGTH = 6;
  const MAX_PASSWORD_LENGTH = 32;

  /**
   * @Route("/{flavor}/", name="index", requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function indexAction(Request $request)
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
              if ($request->get('flavor'))
              {
                  $info['url'] = $this->generateUrl('program', array('id' => $program->getProgram()->getId(), 'flavor' => $request->get('flavor')));
              }
              else
              {
                  $info['url'] = $this->generateUrl('catrobat_web_program', array('id' => $program->getProgram()->getId()));
              }
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
   * @Route("/{flavor}/program/{id}", name="program", requirements={"id":"\d+", "flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function programAction(Request $request, $id, $flavor  = "pocketcode")
  {
    //IMPORTANT: if you change the route '/program' .. also adapt it in ProgramLoader.js (variable: 'program_link')
    //$session = $request->getSession();
    $program = $this->get("programmanager")->find($id);
    $screenshot_repository = $this->get("screenshotrepository");
    $elapsed_time = $this->get("elapsedtime");

    if (!$program) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }

    //TODO increase the View Count only once per Session/User
    $viewed = $request->getSession()->get('viewed', array());
    if(!in_array($program->getId(), $viewed)){
      $program_views_inc = $program->getViews() + 1;
      $this->get("programmanager")->updateProgramViews($program->getId(), $program_views_inc);
      $program->setViews($program_views_inc);
      $viewed[] = $program->getId();
      $request->getSession()->set('viewed', $viewed);
    }

    $program_details = array ();
    $program_details['screenshotBig'] = $screenshot_repository->getScreenshotWebPath($program->getId());
    $program_details['downloadUrl'] = $this->generateUrl('download', array('id' => $program->getId()));
    $program_details['languageVersion'] = $program->getLanguageVersion();
    $program_details['downloads'] = $program->getDownloads();
    $program_details['views'] = $program->getViews();
    $program_details['filesize'] = sprintf("%.2f", $program->getFilesize()/1048576);
    $program_details['age'] = $elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp());

    return $this->get("templating")->renderResponse('::program.html.twig', array("program" => $program, "program_details" => $program_details));
  }

  /**
   * @Route("/search/{q}", name="catrobat_web_search", requirements={"q":".+"})
   * @Route("/{flavor}/search/{q}", name="search", requirements={"q":".+", "flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function searchAction($q)
  {
    return $this->get("templating")->renderResponse('::search.html.twig', array("q" => $q));
  }

  /**
   * @Route("/search/", name="catrobat_web_search_nothing")
   * @Route("/{flavor}/search/", name="empty_search", requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function searchNothingAction()
  {
    return $this->get("templating")->renderResponse('::search.html.twig', array("q" => null));
  }

  /**
   * @Route("/profile/{id}", name="catrobat_web_profile", requirements={"id":"\d+"}, defaults={"id" = 0})
   * @Route("/{flavor}/profile/{id}", name="profile", requirements={"id":"\d+", "flavor": "pocketcode|pocketkodey"}, defaults={"id" = 0})
   * @Method({"GET"})
   */
  public function profileAction(Request $request, $id)
  {
    $twig = '::profile.html.twig';


    if($id == 0) {
      $user = $this->getUser();
      $twig = '::myprofile.html.twig';
    }
    else
      $user = $this->get("usermanager")->find($id);

    if (!$user)
      return $this->redirectToRoute('fos_user_security_login');

    \Locale::setDefault(substr($request->getLocale(),0,2));
    $country = Intl::getRegionBundle()->getCountryName(strtoupper($user->getCountry()));

    return $this->get("templating")->renderResponse($twig, array(
      "profile" => $user,
      "minPassLength" => self::MIN_PASSWORD_LENGTH,
      "maxPassLength" => self::MAX_PASSWORD_LENGTH,
      "country" => $country
    ));
  }

  /**
   * @Route("/profileSave", name="catrobat_web_profile_save")
   * @Route("/{flavor}/profileSave", name="profile_save", requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"POST"})
   */
  public function profileSaveAction(Request $request)
  {
    /**
     * @var $user \Catrobat\AppBundle\Entity\User
     */
    $user = $this->getUser();
    if(!$user)
      return $this->redirectToRoute('fos_user_security_login');

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
   * @Route("/profileDeleteProgram/{id}", name="catrobat_web_profile_delete_program", requirements={"id":"\d+"}, defaults={"id" = 0})
   * @Route("/{flavor}/profileDeleteProgram/{id}", name="profile_delete_program", requirements={"id":"\d+", "flavor": "pocketcode|pocketkodey"}, defaults={"id" = 0})
   * @Method({"GET"})
   */
  public function deleteProgramAction($id)
  {
    /**
     * @var $user \Catrobat\AppBundle\Entity\User
     * @var $program \Catrobat\AppBundle\Entity\Program
     */
    if($id == 0)
      return $this->redirectToRoute('catrobat_web_profile');

    $user = $this->getUser();
    if(!$user)
      return $this->redirectToRoute('fos_user_security_login');

    $program = $user->getPrograms()->matching(Criteria::create()
      ->where(Criteria::expr()->eq("id", $id)));

    if(!$program)
      throw $this->createNotFoundException('Unable to find Project entity.');

    $program->setVisible(false);

    $em = $this->getDoctrine()->getEntityManager();
    $em->persist($program);
    $em->flush();

    return $this->redirectToRoute('catrobat_web_profile');
  }

  /**
   * @Route("/termsOfUse", name="catrobat_web_termsOfUse")
   * @Route("/{flavor}/termsOfUse", name="termsOfUse", requirements={"flavor": "pocketcode|pocketkodey"})
   * @Method({"GET"})
   */
  public function termsOfUseAction()
  {
    return $this->get("templating")->renderResponse('::termsOfUse.html.twig');
  }

  /**
   * @Route("/licenseToPlay", name="catrobat_web_licenseToPlay")
   * @Route("/{flavor}/licenseToPlay", name="licenseToPlay", requirements={"flavor": "pocketcode|pocketkodey"})
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

  public function country($code, $locale = null)
  {
    $countries = Locale::getDisplayCountries($locale ?: $this->localeDetector->getLocale());
    if (array_key_exists($code, $countries)) {
      return $this->fixCharset($countries[$code]);
    }
    return '';
  }

}
