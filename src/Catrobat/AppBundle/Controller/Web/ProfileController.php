<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\Entity\FollowNotification;
use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\StatusCode;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;

class ProfileController extends Controller
{
  const MIN_PASSWORD_LENGTH = 6;
  const MAX_PASSWORD_LENGTH = 32;
  const MAX_UPLOAD_SIZE = 5242880; // 5*1024*1024
  const MAX_AVATAR_SIZE = 300;

  /**
   * @Route("/profile/{id}", name="profile", requirements={"id":"\d+"}, defaults={"id" = 0}, methods={"GET"})
   * @Route("/profile/")  // Overwrite for FosUser Profile Route (We don't use it!)
   *
   * @param Request $request
   * @param integer $id
   *
   * @throws \Exception
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function profileAction(Request $request, $id = 0)
  {
    /**
     * @var $user User
     */
    $id = (integer)$id;
    $twig = 'profile/profileHandler.html.twig';
    $my_profile = false;
    $program_count = 0;

    if ($id === 0 || ($this->getUser() && $this->getUser()->getId() === $id))
    {
      $user = $this->getUser();
      $my_profile = true;
    }
    else
    {
      $user = $this->get('usermanager')->find($id);
      $program_count = count($this->get('programmanager')->getUserPrograms($id));
    }

    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $oauth_user = $user->getFacebookUid() || $user->getGplusUid();

    \Locale::setDefault(substr($request->getLocale(), 0, 2));
    $country = Intl::getRegionBundle()->getCountryName(strtoupper($user->getCountry()));
    $firstMail = $user->getEmail();
    $secondMail = $user->getAdditionalEmail();
    $nolb_user = $user->getNolbUser();
    $followerCount = $user->getFollowers()->count();

    return $this->get('templating')->renderResponse($twig, [
      'profile'        => $user,
      'program_count'  => $program_count,
      'follower_count' => $followerCount,
      'country'        => $country,
      'firstMail'      => $firstMail,
      'secondMail'     => $secondMail,
      'oauth_user'     => $oauth_user,
      'nolb_user'      => $nolb_user,
      'minPassLength'  => self::MIN_PASSWORD_LENGTH,
      'maxPassLength'  => self::MAX_PASSWORD_LENGTH,
      'username'       => $user->getUsername(),
      'myProfile'      => $my_profile,
    ]);
  }


  /**
   * @Route("/countrySave", name="country_save", methods={"POST"})
   *
   * @param Request $request
   *
   * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function countrySaveAction(Request $request)
  {
    /**
     * @var $user User
     */
    $user = $this->getUser();

    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $country = $request->request->get('country');

    try
    {
      $this->validateCountryCode($country);
    } catch (\Exception $e)
    {
      return JsonResponse::create([
        'statusCode' => $e->getMessage(),
      ]);
    }

    $user->setCountry($country);

    $this->get('usermanager')->updateUser($user);

    return JsonResponse::create([
      'statusCode' => StatusCode::OK,
    ]);
  }

  /**
   * @Route("/passwordSave", name="password_save", methods={"POST"})
   *
   * @param Request $request
   *
   * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function passwordSaveAction(Request $request)
  {
    /**
     * @var \Catrobat\AppBundle\Entity\User        $user
     * @var \Catrobat\AppBundle\Entity\UserManager $userManager
     */
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $old_password = $request->request->get('oldPassword');

    $factory = $this->get('security.encoder_factory');
    $encoder = $factory->getEncoder($user);

    $bool = $encoder->isPasswordValid($user->getPassword(), $old_password, $user->getSalt());


    if (!$bool)
    {
      return JsonResponse::create([
        'statusCode' => StatusCode::PASSWORD_INVALID,
      ]);
    }

    $newPassword = $request->request->get('newPassword');
    $repeatPassword = $request->request->get('repeatPassword');

    try
    {
      $this->validateUserPassword($newPassword, $repeatPassword);
    } catch (\Exception $e)
    {
      return JsonResponse::create([
        'statusCode' => $e->getMessage(),
      ]);
    }

    if ($newPassword !== '')
    {
      $user->setPlainPassword($newPassword);
    }

    $this->get('usermanager')->updateUser($user);

    return JsonResponse::create([
      'statusCode'     => StatusCode::OK,
      'saved_password' => "supertoll",
    ]);
  }


  /**
   * @Route("/emailSave", name="email_save", methods={"POST"})
   *
   * @param Request $request
   *
   * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function emailSaveAction(Request $request)
  {
    /**
     * @var \Catrobat\AppBundle\Entity\User
     */
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $firstMail = $request->request->get('firstEmail');
    $secondMail = $request->request->get('secondEmail');

    if ($firstMail === '' && $secondMail === '')
    {
      return JsonResponse::create(['statusCode' => StatusCode::USER_EMAIL_MISSING]);
    }

    try
    {
      $this->validateEmail($firstMail);
    } catch (\Exception $e)
    {
      return JsonResponse::create(['statusCode' => $e->getMessage(), 'email' => 1]);
    }
    try
    {
      $this->validateEmail($secondMail);
    } catch (\Exception $e)
    {
      return JsonResponse::create(['statusCode' => $e->getMessage(), 'email' => 2]);
    }

    if ($this->checkEmailExists($firstMail))
    {
      return JsonResponse::create(['statusCode' => StatusCode::USER_EMAIL_ALREADY_EXISTS, 'email' => 1]);
    }
    if ($this->checkEmailExists($secondMail))
    {
      return JsonResponse::create(['statusCode' => StatusCode::USER_EMAIL_ALREADY_EXISTS, 'email' => 2]);
    }

    if ($firstMail !== '' && $firstMail !== $user->getEmail())
    {
      $user->setEmail($firstMail);
    }
    if ($firstMail !== '' && $secondMail !== '' && $secondMail !== $user->getAdditionalEmail())
    {
      $user->setAdditionalEmail($secondMail);
    }
    if ($firstMail !== '' && $secondMail === '')
    {
      $user->setAdditionalEmail('');
    }
    if ($firstMail === '' && $secondMail === '' && $user->getAdditionalEmail() !== '')
    {
      $user->setEmail($user->getAdditionalEmail());
      $user->setAdditionalEmail('');
    }
    if ($firstMail === '' && $secondMail !== '')
    {
      $user->setEmail($secondMail);
      $user->setAdditionalEmail('');
    }

    $this->get('usermanager')->updateUser($user);

    return JsonResponse::create([
      'statusCode' => StatusCode::OK,
    ]);
  }


  /**
   * @Route("/profileUploadAvatar", name="profile_upload_avatar", methods={"POST"})
   *
   * @param Request $request
   *
   * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function uploadAvatarAction(Request $request)
  {
    /**
     * @var $user User
     */
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $image_base64 = $request->request->get('image');

    try
    {
      $image_base64 = $this->checkAndResizeBase64Image($image_base64);
    } catch (\Exception $e)
    {
      return JsonResponse::create(['statusCode' => $e->getMessage()]);
    }

    $user->setAvatar($image_base64);
    $this->get('usermanager')->updateUser($user);

    return JsonResponse::create([
      'statusCode'   => StatusCode::OK,
      'image_base64' => $image_base64,
    ]);
  }


  /**
   * @Route("/deleteAccount", name="profile_delete_account", methods={"POST"})
   *
   * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function deleteAccountAction()
  {
    /**
     * @var $user User
     */
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $em = $this->getDoctrine()->getManager();

    $user_id = $user->getId();
    $user_comments = $this->getDoctrine()
      ->getRepository('AppBundle:UserComment')
      ->findBy(['userId' => $user_id], ['id' => 'DESC']);

    foreach ($user_comments as $comment)
    {
      $em->remove($comment);
    }

    $em->remove($user);
    $em->flush();

    return JsonResponse::create([
      'statusCode' => StatusCode::OK,
      'count'      => count($user_comments),
    ]);
  }


  /**
   * @Route("/followUser/{id}", name="follow_user", methods = {"GET"}, requirements={"id":"\d+"}, defaults={"id" = 0})
   *
   * @param         $id
   *
   * @throws \Exception
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function followUser($id)
  {
    /**
     * @var User $user
     */
    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    if ($id === 0 || $id === $user->getId())
    {
      return $this->redirectToRoute('profile');
    }

    /**
     * @var $userToFollow User
     */
    $userToFollow = $this->get('usermanager')->find($id);
    $user->addFollowing($userToFollow);
    $this->get('usermanager')->updateUser($user);

    $notification_service = $this->get("catro_notification_service");
    $notification = new FollowNotification(
      $userToFollow,
      "Follow notification",
      "follows you now",
      $user
    );
    $notification_service->addNotification($notification);

    return $this->redirectToRoute('profile', ['id' => $id]);
  }


  /**
   * @Route("/unfollowUser/{id}", name="unfollow_user", methods = {"GET"},requirements={"id":"\d+"}, defaults={"id" = 0})
   *
   * @param Request $request
   * @param         $id
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   */
  public function unfollowUser($id)
  {

    $user = $this->getUser();
    if (!$user)
    {
      return $this->redirectToRoute('fos_user_security_login');
    }

    if ($id === 0)
    {
      return $this->redirectToRoute('profile');
    }

    /**
     * @var $userToUnfollow User
     */
    $userToUnfollow = $this->get('usermanager')->find($id);
    $user->removeFollowing($userToUnfollow);
    $this->get('usermanager')->updateUser($user);

    return $this->redirectToRoute('profile', ['id' => $id]);
  }


  /**
   * @Route("/follow/{type}", name="list_follow", methods = {"POST"}, defaults={"_format": "json"}, requirements={"type":"follower|follows"})
   *
   * @param Request $request
   * @param         $type
   *
   * @return JsonResponse
   */
  public function listFollow(Request $request, $type)
  {
    $criteria = Criteria::create()
      ->orderBy(["username" => Criteria::ASC])
      ->setFirstResult($request->get("page") * $request->get("pageSize"))
      ->setMaxResults($request->get("pageSize"));

    /**
     * @var User            $user
     * @var ArrayCollection $followCollection
     * @var User[]          $users
     */
    $user = $this->get('usermanager')->find($request->get("id"));
    switch ($type)
    {
      case "follower":
        $followCollection = $user->getFollowers();
        break;
      case "follows":
        $followCollection = $user->getFollowing();
        break;
    }
    $length = $followCollection->count();
    $followCollection->first();
    $users = $followCollection->matching($criteria)->toArray();

    $data = [];
    foreach ($users as $user)
    {
      array_push($data, [
        "username" => $user->getUsername(),
        "id"       => $user->getId(),
        "avatar"   => $user->getAvatar(),
      ]);
    }

    return JsonResponse::create(["profiles" => $data, "maximum" => $length]);
  }


  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //// private functions
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


  /**
   * @param $pass1
   * @param $pass2
   *
   * @throws \Exception
   */
  private function validateUserPassword($pass1, $pass2)
  {
    if ($pass1 !== $pass2)
    {
      throw new \Exception(StatusCode::USER_PASSWORD_NOT_EQUAL_PASSWORD2);
    }

    if (strcasecmp($this->getUser()->getUsername(), $pass1) === 0)
    {
      throw new \Exception(StatusCode::USER_USERNAME_PASSWORD_EQUAL);
    }

    if ($pass1 !== '' && strlen($pass1) < self::MIN_PASSWORD_LENGTH)
    {
      throw new \Exception(StatusCode::USER_PASSWORD_TOO_SHORT);
    }

    if ($pass1 !== '' && strlen($pass1) > self::MAX_PASSWORD_LENGTH)
    {
      throw new \Exception(StatusCode::USER_PASSWORD_TOO_LONG);
    }
  }


  /**
   * @param $email
   *
   * @throws \Exception
   */
  private function validateEmail($email)
  {
    $name = '[a-zA-Z0-9]((\.|\-|_)?[a-zA-Z0-9])*';
    $domain = '[a-zA-Z]((\.|\-)?[a-zA-Z0-9])*';
    $tld = '[a-zA-Z]{2,8}';
    $regEx = '/^(' . $name . ')@(' . $domain . ')\.(' . $tld . ')$/';

    if (!preg_match($regEx, $email) && !empty($email))
    {
      throw new \Exception(StatusCode::USER_EMAIL_INVALID);
    }
  }


  /**
   * @param $country
   *
   * @throws \Exception
   */
  private function validateCountryCode($country)
  {
    //todo: check if code is really from the drop-down
    if (!empty($country) && !preg_match('/[a-zA-Z]{2}/', $country))
    {
      throw new \Exception(StatusCode::USER_COUNTRY_INVALID);
    }
  }


  /**
   * @param $email
   *
   * @return bool
   */
  private function checkEmailExists($email)
  {
    if ($email === '')
    {
      return false;
    }

    $userWithFirstMail = $this->get('usermanager')->findOneBy(['email' => $email]);
    $userWithSecondMail = $this->get('usermanager')->findOneBy(['additional_email' => $email]);

    if ($userWithFirstMail !== null && $userWithFirstMail !== $this->getUser() || $userWithSecondMail !== null && $userWithSecondMail !== $this->getUser())
    {
      return true;
    }

    return false;
  }

  /**
   * @param      $code
   * @param null $locale
   *
   * @return string
   */
  public function country($code, $locale = null)
  {
    $countries = Intl::getRegionBundle()->getCountryNames($locale ?: $this->localeDetector->getLocale());
    if (array_key_exists($code, $countries))
    {
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

    if (!preg_match($data_regx, $image_data[0]))
    {
      throw new \Exception(StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
    }

    $image_type = preg_replace('/data:(.+)/', '\\1', $image_data[0]);
    $image = null;

    switch ($image_type)
    {
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

    if (!$image)
    {
      throw new \Exception(StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
    }

    $image_data = preg_replace($data_regx, '\\2', $image_base64);
    $image_size = strlen(base64_decode($image_data));

    if ($image_size > self::MAX_UPLOAD_SIZE)
    {
      throw new \Exception(StatusCode::UPLOAD_EXCEEDING_FILESIZE);
    }

    $width = imagesx($image);
    $height = imagesy($image);

    if ($width === 0 || $height === 0)
    {
      throw new \Exception(StatusCode::UPLOAD_UNSUPPORTED_FILE_TYPE);
    }

    if (max($width, $height) > self::MAX_AVATAR_SIZE)
    {
      $new_image = imagecreatetruecolor(self::MAX_AVATAR_SIZE, self::MAX_AVATAR_SIZE);
      if (!$new_image)
      {
        throw new \Exception(StatusCode::USER_AVATAR_UPLOAD_ERROR);
      }

      imagesavealpha($new_image, true);
      imagefill($new_image, 0, 0, imagecolorallocatealpha($new_image, 0, 0, 0, 127));

      if (!imagecopyresized($new_image, $image, 0, 0, 0, 0, self::MAX_AVATAR_SIZE, self::MAX_AVATAR_SIZE, $width, $height))
      {
        imagedestroy($new_image);
        throw new \Exception(StatusCode::USER_AVATAR_UPLOAD_ERROR);
      }

      ob_start();
      if (!imagepng($new_image))
      {
        imagedestroy($new_image);
        throw new \Exception(StatusCode::USER_AVATAR_UPLOAD_ERROR);
      }

      $image_base64 = 'data:image/png;base64,' . base64_encode(ob_get_clean());
    }

    return $image_base64;
  }
}
