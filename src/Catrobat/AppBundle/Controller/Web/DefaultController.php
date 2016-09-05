<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\Entity\UserComment;
use Catrobat\AppBundle\StatusCode;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Catrobat\AppBundle\Services\FeaturedImageRepository;
use Catrobat\AppBundle\Entity\FeaturedRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\Response;
use Catrobat\AppBundle\Entity\UserManager;

class DefaultController extends Controller
{
    const MIN_PASSWORD_LENGTH = 6;
    const MAX_PASSWORD_LENGTH = 32;
    const MAX_UPLOAD_SIZE = 5242880; // 5*1024*1024
    const MAX_AVATAR_SIZE = 300;

  /**
   * @Route("/", name="index")
   * @Method({"GET"})
   */
  public function indexAction(Request $request)
  {
      /**
       * @var $image_repository FeaturedImageRepository
       * @var $repository FeaturedRepository
       */
      $image_repository = $this->get('featuredimagerepository');
      $repository = $this->get('featuredrepository');

      $featured_items = $repository->getFeaturedItems($request->getSession()->get('flavor'), 5, 0);

      $featured = array();
      foreach ($featured_items as $item) {
          $info = array();
          if ($item->getProgram() !== null) {
              if ($request->get('flavor')) {
                  $info['url'] = $this->generateUrl('program', array('id' => $item->getProgram()->getId(), 'flavor' => $request->get('flavor')));
              } else {
                  $info['url'] = $this->generateUrl('catrobat_web_program', array('id' => $item->getProgram()->getId()));
              }
          } else {
              $info['url'] = $item->getUrl();
          }
          $info['image'] = $image_repository->getWebPath($item->getId(), $item->getImageType());

          $featured[] = $info;
      }

      return $this->get('templating')->renderResponse('::index.html.twig', array('featured' => $featured));
  }

  /**
   * @Route("/program/{id}", name="program", requirements={"id":"\d+"})
   * @Route("/details/{id}", name="catrobat_web_detail", requirements={"id":"\d+"})
   * @Method({"GET"})
   */
  public function programAction(Request $request, $id, $flavor = 'pocketcode')
  {
    /**
   * @var $user User
   * @var $program Program
   * @var $reported_program ProgramInappropriateReport
   * @var $gamejam GameJam
   */
    $program = $this->get('programmanager')->find($id);
    $screenshot_repository = $this->get('screenshotrepository');
    $elapsed_time = $this->get('elapsedtime');

    if (!$program || !$program->isVisible()) {
        throw $this->createNotFoundException('Unable to find Project entity.');
    }

    $viewed = $request->getSession()->get('viewed', array());
    if (!in_array($program->getId(), $viewed)) {
        $this->get('programmanager')->increaseViews($program);
        $viewed[] = $program->getId();
        $request->getSession()->set('viewed', $viewed);
    }
    $referrer = $request->headers->get('referer');
    $request->getSession()->set('referer', $referrer);

    $program_comments_ = $this->getDoctrine()
      ->getRepository('AppBundle:UserComment')
      ->findBy(
        array('programId' => $program->getId()),array('id' => 'DESC'));
    $program_details = array(
      'screenshotBig' => $screenshot_repository->getScreenshotWebPath($program->getId()),
      'downloadUrl' => $this->generateUrl('download', array('id' => $program->getId(), 'fname' => $program->getName())),
      'languageVersion' => $program->getLanguageVersion(),
      'downloads' => $program->getDownloads() + $program->getApkDownloads(),
      'views' => $program->getViews(),
      'filesize' => sprintf('%.2f', $program->getFilesize() / 1048576),
      'age' => $elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp()),
      'referrer' => $referrer,
      'id' => $program->getId(),
      'comments' => $program_comments_,
      'commentsLength' =>  count($program_comments_),
      'isAdmin' => $this->isGranted("ROLE_ADMIN"),
    );

    $user = $this->getUser();
    $user_programs = null;
    if ($user) {
        $user_programs = $user->getPrograms()->matching(Criteria::create()
          ->where(Criteria::expr()->eq('id', $program->getId())));
    }

    $isReportedByUser = false;
    $em = $this->getDoctrine()->getManager();
    $reported_program = $em->getRepository("\Catrobat\AppBundle\Entity\ProgramInappropriateReport")
    ->findOneBy(array('program' => $program->getId()));

    if ($reported_program) {
        $isReportedByUser = ($user == $reported_program->getReportingUser());
    }

	  $program_url = $this->generateUrl('program', array('id' => $program->getId()), true);
    $share_text = trim($program->getName() . ' on ' . $program_url . ' ' . $program->getDescription());

    $jam = null;
    $gamejam = $this->get('gamejamrepository')->getCurrentGameJam();

    if($gamejam) {
      $config = $this->container->getParameter('gamejam');
      $gamejam_id = $gamejam->getId();
      $gamejam_config = $config[$gamejam_id];

      $logo_url = $gamejam_config['logo_url'];
      $display_name = $gamejam_config['display_name'];
      $gamejam_url = $gamejam_config['gamejam_url'];

      $jam = array(
          'name' => $display_name,
          'logo_url' => $logo_url,
          'gamejam_url' => $gamejam_url
      );
    }

    return $this->get('templating')->renderResponse('::program.html.twig', array(
        'program' => $program,
        'program_details' => $program_details,
        'my_program' => count($user_programs) > 0 ? true : false,
        'already_reported' => $isReportedByUser,
        'shareText' => $share_text,
        'jam' => $jam
    ));
  }

    /**
     * Route("/program/{id}/codeview", name="show_code_view", requirements={"id":".+"})
     * Method({"GET"})
     *
     * public function codeViewAction($id)
     * {
     * $program = $this->get('programmanager')->find($id);
     * $extractedFileRepository = $this->get('extractedfilerepository');
     * $extractedProgram = $extractedFileRepository->loadProgramExtractedFile($program);
     *
     * $codeObjects = $extractedProgram->getCodeObjects();
     * $twigParams = null;
     *
     * if (!empty($codeObjects)) {
     * $objectList = array();
     * foreach ($codeObjects as $key => $codeObject) {
     * if ($key === 0) {
     * $background = array(
     * 'name' => $codeObject->getName(),
     * 'icon_url' => 'iconToDisplayURL',
     * 'scripts' => $codeObject->getScripts()
     * );
     * } else {
     * $objectList[] = array(
     * 'name' => $codeObject->getName(),
     * 'icon_url' => 'iconToDisplayURL',
     * 'scripts' => $codeObject->getScripts()
     * );
     * }
     * }
     *
     * $debug = null;
     *
     * $twigParams = array(
     * 'contains_objects' => true,
     * 'program' => $program,
     * 'code_objects' => $extractedProgram->getContainingCodeObjects(),
     * 'debug' => $debug,
     * 'background' => $background,
     * 'object_list' => $objectList
     * );
     *
     *
     * } else {
     * $twigParams = array(
     * 'containsObjects' => false
     * );
     * }
     *
     * return $this->get('templating')->renderResponse('::codeview.html.twig', $twigParams);
     * }
     */

  /**
   * @Route("/search/{q}", name="search", requirements={"q":".+"})
   * @Method({"GET"})
   */
  public function searchAction($q)
  {
      return $this->get('templating')->renderResponse('::search.html.twig', array('q' => $q));
  }

  /**
   * @Route("/search/", name="empty_search")
   * @Method({"GET"})
   */
  public function searchNothingAction()
  {
      return $this->get('templating')->renderResponse('::search.html.twig', array('q' => null));
  }
    
  /**
   * @Route("/report", name="report")
   * @Method({"GET"})
   */
  public function reportCommentAction(Request $request)
  {
    $user = $this->getUser();
    if (!$user) {
      return new Response("log_in");
    }

    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository('AppBundle:UserComment')->find($_GET['CommentId']);

    if (!$comment) {
      throw $this->createNotFoundException(
        'No comment found for this id '.$_GET['CommentId']
      );
    }

    $comment->setIsReported(true);
    $em->flush();
    return new Response("Comment successfully reported!");
  }

  /**
   * @Route("/delete", name="delete")
   * @Method({"GET"})
   */
  public function deleteCommentAction(Request $request)
  {
    $user = $this->getUser();
    if (!$user) {
      return new Response("log_in");
    }

    if (!$this->isGranted("ROLE_ADMIN"))
    {
      return new Response("no_admin");
    }
    $em = $this->getDoctrine()->getManager();
    $comment = $em->getRepository('AppBundle:UserComment')->find($_GET['CommentId']);

    if (!$comment) {
      throw $this->createNotFoundException(
        'No comment found for this id '.$_GET['CommentId']
      );
    }
    $em->remove($comment);
    $em->flush();
    return new Response("ok");
  }

  /**
   * @Route("/comment", name="comment")
   * @Method({"POST"})
   */
  public function postCommentAction(Request $request)
  {
    $user = $this->getUser();
    if (!$user) {
      return new Response("log_in");
    }

    $token = $this->get("security.token_storage")->getToken();
    $user = $token->getUser();
    $id = $user->getId();

    /**
     * @var $user User
     * @var $program Program
     * @var $reported_program ProgramInappropriateReport
     */

    $temp_comment = new UserComment();
    $temp_comment->setUsername($user->getUsername());
    $temp_comment->setUserId($id);
    $temp_comment->setText($_POST['Message']);
    $temp_comment->setProgramId($_POST['ProgramId']);
    $temp_comment->setUploadDate(date_create());
    $temp_comment->setIsReported(false);

    $em = $this->getDoctrine()->getManager();
    $em->persist($temp_comment);
    $em->flush();
    return new Response("ok");
  }

  /**
   * @Route("/profile/{id}", name="profile", requirements={"id":"\d+"}, defaults={"id" = 0})
   * @Method({"GET"})
   */
  public function profileAction(Request $request, $id)
  {

      /**
       * @var $user User
       */
      $twig = '::profile.html.twig';
      $program_count = 0;

      if ($id == 0) {
          $user = $this->getUser();
          $twig = '::myprofile.html.twig';
      } else {
          $user = $this->get('usermanager')->find($id);
          $program_count = count($this->get('programmanager')->getUserPrograms($id));
      }

      if (!$user) {
          return $this->redirectToRoute('fos_user_security_login');
      }

      $oauth_user = $user->getFacebookUid() || $user->getGplusUid();

      \Locale::setDefault(substr($request->getLocale(), 0, 2));
      $country = Intl::getRegionBundle()->getCountryName(strtoupper($user->getCountry()));
      $firstMail = $user->getEmail();
      $secondMail = $user->getAdditionalEmail();



      return $this->get('templating')->renderResponse($twig, array(
      'profile' => $user,
      'program_count' => $program_count,
      'minPassLength' => self::MIN_PASSWORD_LENGTH,
      'maxPassLength' => self::MAX_PASSWORD_LENGTH,
      'country' => $country,
      'firstMail' => $firstMail,
      'secondMail' => $secondMail,
      'oauth_user' => $oauth_user,
    ));
  }


  /**
   * @Route("/profile/{id}/edit", name="profile_edit", requirements={"id":"\d+"}, defaults={"id" = 0})
   * @Method({"GET"})
   */
  public function profileEditAction(Request $request, $id)
  {

    /**
     * @var $user User
     */
    $twig = '::myprofileEdit.html.twig';

    if ($id == 0) {
      $user = $this->getUser();
    } else {
      $user = $this->get('usermanager')->find($id);
    }

    if (!$user) {
      return $this->redirectToRoute('fos_user_security_login');
    }

    \Locale::setDefault(substr($request->getLocale(), 0, 2));
    $country = Intl::getRegionBundle()->getCountryName(strtoupper($user->getCountry()));
    $firstMail = $user->getEmail();
    $secondMail = $user->getAdditionalEmail();
    $username = $user->getUsername();



    return $this->get('templating')->renderResponse($twig, array(
        'profile' => $user,
        'minPassLength' => self::MIN_PASSWORD_LENGTH,
        'maxPassLength' => self::MAX_PASSWORD_LENGTH,
        'country' => $country,
        'firstMail' => $firstMail,
        'secondMail' => $secondMail,
        'username' => $username,
    ));
  }

  /**
   * @Route("/nameEdit", name="name_edit")
   * @Method({"GET"})
   */
  public function NameEditAction(Request $request){
    $twig = '::nameEdit.html.twig';
    return $this->get('templating')->renderResponse($twig);
  }


  /**
   * @Route("/emailEdit", name="email_edit")
   * @Method({"GET"})
   */
  public function EmailEditAction(Request $request){
    $twig = '::emailEdit.html.twig';
    return $this->get('templating')->renderResponse($twig);
  }

  /**
   * @Route("/avatarEdit", name="avatar_edit")
   * @Method({"GET"})
   */
  public function AvatarEditAction(Request $request){
    $twig = '::avatarEdit.html.twig';
    return $this->get('templating')->renderResponse($twig);
  }


  /**
   * @Route("/passwordEdit", name="password_edit")
   * @Method({"GET"})
   */
  public function passwordEditAction(Request $request){
    $twig = '::passwordEdit.html.twig';
    return $this->get('templating')->renderResponse($twig, array(
        'minPassLength' => self::MIN_PASSWORD_LENGTH,
        'maxPassLength' => self::MAX_PASSWORD_LENGTH,
    ));
  }


  /**
   * @Route("/countryEdit", name="country_edit")
   * @Method({"GET"})
   */
  public function countryEditAction(Request $request){
    $twig = '::countryEdit.html.twig';
    return $this->get('templating')->renderResponse($twig);
  }


  /**
   * @Route("/nameSave", name="name_save")
   * @Method({"POST"})
   */
  public function NameSaveAction(Request $request){
    /**
     * @var $user User
     */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $userManager = $this->get("usermanager");
    $em = $this->getDoctrine()->getManager();
    $user_comments = $em->getRepository('AppBundle:UserComment')->findBy(array('username' => $user->getUsername()));

    $new_user_name = $request->request->get('new_user_name');
    if(strlen($new_user_name) == 0) {
      return JsonResponse::create(array('statusCode' => '776'));
    }

    if ($userManager->findUserByUsername($new_user_name) != null) {
      return JsonResponse::create(array('statusCode' => '777'));
    }

    foreach ($user_comments as $comment){
      $comment->setUsername($new_user_name);
    }

    $em->flush();
    $user->setUsername($new_user_name);
    $this->get('usermanager')->updateUser($user);
    return JsonResponse::create(array('statusCode' => StatusCode::OK));
  }

  /**
   * @Route("/countrySave", name="country_save")
   * @Method({"POST"})
   */
  public function countrySaveAction(Request $request){
    /**
     * @var $user User
     */
    $user = $this->getUser();

    if (!$user) {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $country = $request->request->get('country');

    try {
      $this->validateCountryCode($country);
    } catch (\Exception $e) {
      return JsonResponse::create(array('statusCode' => $e->getMessage()));
    }

    $user->setCountry($country);

    $this->get('usermanager')->updateUser($user);

    return JsonResponse::create(array('statusCode' => StatusCode::OK));
  }

  /**
   * @Route("/passwordSave", name="password_save")
   * @Method({"POST"})
   */
  public function passwordSaveAction(Request $request)
  {
    /**
     * @var \Catrobat\AppBundle\Entity\User
     */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('fos_user_security_login');
    }
/*
    try{
      if ($oldcredentials !== $request->request->get('oldPassword')) {
        throw new \Exception(StatusCode::USER_PASSWORD_NOT_EQUAL_PASSWORD2);
      }
    } catch (\Exception $e) {
      return JsonResponse::create(array('statusCode' => $e->getMessage()));
    }*/

    $user_manager = $this->get('fos_user.user_manager');
    $encoder_factory = $this->get('security.encoder_factory');

    $encoder = $encoder_factory->getEncoder($user);
    $salt = $user->getSalt();
    $old_password = $request->request->get('oldPassword');

    $encoded_password = $encoder->encodePassword($old_password, $salt);

    if ($encoded_password !== $user->getPassword()) {
      return JsonResponse::create(array('statusCode' => "777",));
    }

    $newPassword = $request->request->get('newPassword');
    $repeatPassword = $request->request->get('repeatPassword');

    try {
      $this->validateUserPassword($newPassword, $repeatPassword);
    } catch (\Exception $e) {
      return JsonResponse::create(array('statusCode' => $e->getMessage()));
    }

    if ($newPassword !== '') {
      $user->setPlainPassword($newPassword);
    }

    $this->get('usermanager')->updateUser($user);

    return JsonResponse::create(array('statusCode' => StatusCode::OK, 'saved_password' => "supertoll"));
  }


  /**
   * @Route("/emailSave", name="email_save")
   * @Method({"POST"})
   */
  public function emailSaveAction(Request $request){
    /**
     * @var \Catrobat\AppBundle\Entity\User
     */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $firstMail = $request->request->get('firstEmail');
    $secondMail = $request->request->get('secondEmail');

    try {
      $this->validateEmail($firstMail);
      $this->validateEmail($secondMail);
    } catch (\Exception $e) {
      return JsonResponse::create(array('statusCode' => $e->getMessage()));
    }

    if ($firstMail === '' && $secondMail === '') {
      return JsonResponse::create(array('statusCode' => StatusCode::USER_UPDATE_EMAIL_FAILED));
    }

    if ($this->checkEmailExists($firstMail)) {
      return JsonResponse::create(array('statusCode' => StatusCode::EMAIL_ALREADY_EXISTS, 'email' => 1));
    }
    if ($this->checkEmailExists($secondMail)) {
      return JsonResponse::create(array('statusCode' => StatusCode::EMAIL_ALREADY_EXISTS, 'email' => 2));
    }

    if ($firstMail !== '' && $firstMail != $user->getEmail()) {
      $user->setEmail($firstMail);
    }
    if ($firstMail !== '' && $secondMail !== '' && $secondMail != $user->getAdditionalEmail()) {
      $user->setAdditionalEmail($secondMail);
    }
    if ($firstMail !== '' && $secondMail === '') {
      $user->setAdditionalEmail('');
    }
    if ($firstMail === '' && $secondMail === '' && $user->getAdditionalEmail() !== '') {
      $user->setEmail($user->getAdditionalEmail());
      $user->setAdditionalEmail('');
    }
    if ($firstMail === '' && $secondMail !== '') {
      $user->setEmail($secondMail);
      $user->setAdditionalEmail('');
    }

    $this->get('usermanager')->updateUser($user);

    return JsonResponse::create(array('statusCode' => StatusCode::OK));


  }


  /**
   * @Route("/profileSave", name="profile_save")
   * @Method({"POST"})
   */
  public function profileSaveAction(Request $request)
  {
      /**
     * @var \Catrobat\AppBundle\Entity\User
     */
    $user = $this->getUser();
      if (!$user) {
          return $this->redirectToRoute('fos_user_security_login');
      }

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
      } catch (\Exception $e) {
          return JsonResponse::create(array('statusCode' => $e->getMessage()));
      }

      if ($firstMail === '' && $secondMail === '') {
          return JsonResponse::create(array('statusCode' => StatusCode::USER_UPDATE_EMAIL_FAILED));
      }

      if ($this->checkEmailExists($firstMail)) {
          return JsonResponse::create(array('statusCode' => StatusCode::EMAIL_ALREADY_EXISTS, 'email' => 1));
      }
      if ($this->checkEmailExists($secondMail)) {
          return JsonResponse::create(array('statusCode' => StatusCode::EMAIL_ALREADY_EXISTS, 'email' => 2));
      }

      if ($newPassword !== '') {
          $user->setPlainPassword($newPassword);
      }
      if ($firstMail !== '' && $firstMail != $user->getEmail()) {
          $user->setEmail($firstMail);
      }
      if ($firstMail !== '' && $secondMail !== '' && $secondMail != $user->getAdditionalEmail()) {
          $user->setAdditionalEmail($secondMail);
      }
      if ($firstMail !== '' && $secondMail === '') {
          $user->setAdditionalEmail('');
      }
      if ($firstMail === '' && $secondMail === '' && $user->getAdditionalEmail() !== '') {
          $user->setEmail($user->getAdditionalEmail());
          $user->setAdditionalEmail('');
      }
      if ($firstMail === '' && $secondMail !== '') {
          $user->setEmail($secondMail);
          $user->setAdditionalEmail('');
      }

      $user->setCountry($country);

      $this->get('usermanager')->updateUser($user);

      return JsonResponse::create(array('statusCode' => StatusCode::OK));
  }

  /**
   * @Route("/profileDeleteProgram/{id}", name="profile_delete_program", requirements={"id":"\d+"}, defaults={"id" = 0})
   * @Method({"GET"})
   */
  public function deleteProgramAction($id)
  {
      /*
     * @var $user \Catrobat\AppBundle\Entity\User
     * @var $program \Catrobat\AppBundle\Entity\Program
     */
    if ($id == 0) {
        return $this->redirectToRoute('profile');
    }

      $user = $this->getUser();
      if (!$user) {
          return $this->redirectToRoute('fos_user_security_login');
      }

      $programs = $user->getPrograms()->matching(Criteria::create()
      ->where(Criteria::expr()->eq('id', $id)));

      $program = $programs[0];
      if (!$program) {
          throw $this->createNotFoundException('Unable to find Project entity.');
      }

      $program->setVisible(false);

      $em = $this->getDoctrine()->getManager();
      $em->persist($program);
      $em->flush();

      return $this->redirectToRoute('profile');
  }

  /**
   * @Route("/profileToggleProgramVisibility/{id}", name="profile_toggle_program_visibility", requirements={"id":"\d+"}, defaults={"id" = 0})
   * @Method({"GET"})
   */
  public function toggleProgramVisibilityAction($id)
  {
    /*
     * @var $user \Catrobat\AppBundle\Entity\User
     * @var $program \Catrobat\AppBundle\Entity\Program
     */

     if ($id == 0) {
       return $this->redirectToRoute('profile');
     }

     $user = $this->getUser();
     if (!$user) {
       return $this->redirectToRoute('fos_user_security_login');
     }

     $programs = $user->getPrograms()->matching(Criteria::create()
       ->where(Criteria::expr()->eq('id', $id)));

     $program = $programs[0];
     if (!$program) {
       throw $this->createNotFoundException('Unable to find Project entity.');
     }

     $program->setPrivate(!$program->getPrivate());

     $em = $this->getDoctrine()->getManager();
     $em->persist($program);
     $em->flush();

     return $this->redirectToRoute('profile');
  }


  /**
   * @Route("/profileUploadAvatar", name="profile_upload_avatar")
   * @Method({"POST"})
   */
  public function uploadAvatarAction(Request $request)
  {
    /*
     * @var $user \Catrobat\AppBundle\Entity\User
     */
    $user = $this->getUser();
    if (!$user) {
        return $this->redirectToRoute('fos_user_security_login');
    }

    $image_base64 = $request->request->get('image');

    try {
        $image_base64 = $this->checkAndResizeBase64Image($image_base64);
    } catch (\Exception $e) {
        return JsonResponse::create(array('statusCode' => $e->getMessage()));
    }

    $user->setAvatar($image_base64);
    $this->get('usermanager')->updateUser($user);

    return JsonResponse::create(array(
      'statusCode' => StatusCode::OK,
      'image_base64' => $image_base64,
    ));
  }

  /**
   * @Route("/termsOfUse", name="termsOfUse")
   * @Method({"GET"})
   */
  public function termsOfUseAction()
  {
      return $this->get('templating')->renderResponse('::termsOfUse.html.twig');
  }

  /**
   * @Route("/licenseToPlay", name="licenseToPlay")
   * @Method({"GET"})
   */
  public function licenseToPlayAction()
  {
      return $this->get('templating')->renderResponse('::licenseToPlay.html.twig');
  }

  /**
   * @Route("/pocket-library/{package_name}", name="pocket_library")
   * @Route("/media-library/{package_name}", name="media_package")
   * @Method({"GET"})
   */
  public function MediaPackageAction($package_name, $flavor = 'pocketcode')
  {
    /**
     * @var $package \Catrobat\AppBundle\Entity\MediaPackage
     * @var $file \Catrobat\AppBundle\Entity\MediaPackageFile
     * @var $category \Catrobat\AppBundle\Entity\MediaPackageCategory
     */
    $em = $this->getDoctrine()->getManager();
    $package = $em->getRepository('\Catrobat\AppBundle\Entity\MediaPackage')
      ->findOneBy(array('name_url' => $package_name));

    if (!$package) {
      throw $this->createNotFoundException('Unable to find Package entity.');
    }

    $categories = array();
    foreach($package->getCategories() as $category) {
      $files = array();
      foreach($category->getFiles() as $file) {
        $flavors_arr = preg_replace("/ /", "", $file->getFlavor());
        $flavors_arr = explode(",", $flavors_arr);
        if(!$file->getActive() || ($file->getFlavor() != null && !in_array($flavor, $flavors_arr))) {
          continue;
        }
        $files[] = array(
          'id' => $file->getId(),
          'data' => $file,
          'downloadUrl' => $this->generateUrl('download_media', array(
            'id' => $file->getId(),
            'fname' => $file->getName()
          ))
        );
      }
      $categories[] = array(
        'name' => $category->getName(),
        'files' => $files,
        'priority' => $category->getPriority()
      );
    }

    usort($categories, function($a,$b) {
      if($a['priority'] == $b['priority'])
        return 0;
      return ($a['priority'] > $b['priority']) ? -1 : 1;
    });

    return $this->get('templating')->renderResponse('::mediapackage.html.twig', array(
      'categories' => $categories
    ));
  }

  /**
   * @Route("/gamejame/submit-your-own", name="gamejam_submit_own")
   * @Method({"GET"})
   */
  public function gamejamSubmitOwnAction()
  {
      /**
       * @var $gamejam GameJam
       */
      $jam = null;
      $gamejam = $this->get('gamejamrepository')->getCurrentGameJam();

      if($gamejam) {
          $config = $this->container->getParameter('gamejam');
          $gamejam_id = $gamejam->getId();
          $gamejam_config = $config[$gamejam_id];

          $display_name = $gamejam_config['display_name'];
          $mobile_en_image_url = $gamejam_config['mobile_image_url_en'];
          $mobile_de_image_url = $gamejam_config['mobile_image_url_de'];
          $web_en_image_url = $gamejam_config['web_image_url_en'];
          $web_de_image_url = $gamejam_config['web_image_url_de'];
          $gamejam_url = $gamejam_config['gamejam_url'];
          $gamejam_tag = $gamejam->getHashtag();

          $jam = array(
              'name' => $display_name,
              'mobile_en_image_url' => $mobile_en_image_url,
              'mobile_de_image_url' => $mobile_de_image_url,
              'web_en_image_url' => $web_en_image_url,
              'web_de_image_url' => $web_de_image_url,
              'gamejam_url' => $gamejam_url,
              'tag' => $gamejam_tag
          );

      }
      return $this->get('templating')->renderResponse('::gamejam_submit_own.html.twig', array(
          'jam' => $jam
      ));
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
      if ($pass1 !== $pass2) {
          throw new \Exception(StatusCode::USER_PASSWORD_NOT_EQUAL_PASSWORD2);
      }

      if (strcasecmp($this->getUser()->getUsername(), $pass1) == 0) {
          throw new \Exception(StatusCode::USER_USERNAME_PASSWORD_EQUAL);
      }

      if ($pass1 != '' && strlen($pass1) < self::MIN_PASSWORD_LENGTH) {
          throw new \Exception(StatusCode::USER_PASSWORD_TOO_SHORT);
      }

      if ($pass1 != '' && strlen($pass1) > self::MAX_PASSWORD_LENGTH) {
          throw new \Exception(StatusCode::USER_PASSWORD_TOO_LONG);
      }
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

      if (!preg_match($regEx, $email) && !empty($email)) {
          throw new \Exception(StatusCode::USER_EMAIL_INVALID);
      }
  }

  /*
   * @param string $country
   */
  private function validateCountryCode($country)
  {
      //todo: check if code is really from the drop-down
    if (!empty($country) && !preg_match('/[a-zA-Z]{2}/', $country)) {
        throw new \Exception(StatusCode::USER_COUNTRY_INVALID);
    }
  }

  /*
   * @param string $email
   * @return bool
   */
  private function checkEmailExists($email)
  {
      if ($email === '') {
          return false;
      }

      $userWithFirstMail = $this->get('usermanager')->findOneBy(array('email' => $email));
      $userWithSecondMail = $this->get('usermanager')->findOneBy(array('additional_email' => $email));

      if ($userWithFirstMail != null && $userWithFirstMail != $this->getUser() || $userWithSecondMail != null && $userWithSecondMail != $this->getUser()) {
          return true;
      }

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

  /**
   * @param string $image_base64
   *
   * @throws \Exception
   *
   * @return string
   */
  private function checkAndResizeBase64Image($image_base64)
  {
      $image_data = explode(';base64,', $image_base64);
      $data_regx = '/data:(.+)/';

      if (!preg_match($data_regx, $image_data[0])) {
          throw new \Exception(StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
      }

      $image_type = preg_replace('/data:(.+)/', '\\1', $image_data[0]);
      $image = null;

      switch ($image_type) {
      case 'image/jpg':
      case 'image/jpeg':
        $image = imagecreatefromjpeg($image_base64);
        break;
      case 'image/png':
        $image = imagecreatefrompng($image_base64);
        break;
      case 'image/gif':
        $image = imagecreatefromgif($image_base64);
        break;
      default:
        throw new \Exception(StatusCode::UPLOAD_UNSUPPORTED_MIME_TYPE);
    }

      if (!$image) {
          throw new \Exception(StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
      }

      $image_data = preg_replace($data_regx, '\\2', $image_base64);
      $image_size = strlen(base64_decode($image_data));

      if ($image_size > self::MAX_UPLOAD_SIZE) {
          throw new \Exception(StatusCode::UPLOAD_EXCEEDING_FILESIZE);
      }

      $width = imagesx($image);
      $height = imagesy($image);

      if ($width == 0 || $height == 0) {
          throw new \Exception(StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
      }

      if (max($width, $height) > self::MAX_AVATAR_SIZE) {
          $new_image = imagecreatetruecolor(self::MAX_AVATAR_SIZE, self::MAX_AVATAR_SIZE);
          if (!$new_image) {
              throw new \Exception(StatusCode::USER_AVATAR_UPLOAD_ERROR);
          }

          imagesavealpha($new_image, true);
          imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 0, 0, 0, 127));

          if (!imagecopyresized($new_image, $image, 0, 0, 0, 0, self::MAX_AVATAR_SIZE, self::MAX_AVATAR_SIZE, $width, $height)) {
              imagedestroy($new_image);
              throw new \Exception(StatusCode::USER_AVATAR_UPLOAD_ERROR);
          }

          ob_start();
          if (!imagepng($new_image)) {
              imagedestroy($new_image);
              throw new \Exception(StatusCode::USER_AVATAR_UPLOAD_ERROR);
          }

          $image_base64 = 'data:image/png;base64,'.base64_encode(ob_get_clean());
      }

      return $image_base64;
  }
}
