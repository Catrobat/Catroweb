<?php

namespace Catrobat\AppBundle\Controller\Web;

use Catrobat\AppBundle\Entity\User;
use Catrobat\AppBundle\StatusCode;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;
use Catrobat\AppBundle\Entity\UserManager;

class ProfileController extends Controller
{
    const MIN_PASSWORD_LENGTH = 6;
    const MAX_PASSWORD_LENGTH = 32;
    const MAX_UPLOAD_SIZE = 5242880; // 5*1024*1024
    const MAX_AVATAR_SIZE = 300;

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
        $nolb_user = $user->getNolbUser();

        return $this->get('templating')->renderResponse($twig, array(
            'profile' => $user,
            'program_count' => $program_count,
            'country' => $country,
            'firstMail' => $firstMail,
            'secondMail' => $secondMail,
            'oauth_user' => $oauth_user,
            'nolb_user' => $nolb_user,
        ));
    }


    /**
     * @Route("/profile/edit", name="profile_edit")
     * @Method({"GET"})
     */
    public function profileEditAction(Request $request)
    {

        /**
         * @var $user User
         */
        $twig = '::myprofileEdit.html.twig';

        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('fos_user_security_login');
        }

        \Locale::setDefault(substr($request->getLocale(), 0, 2));
        $country = Intl::getRegionBundle()->getCountryName(strtoupper($user->getCountry()));
        $firstMail = $user->getEmail();
        $secondMail = $user->getAdditionalEmail();
        $username = $user->getUsername();
        $nolb_user = $user->getNolbUser();

        return $this->get('templating')->renderResponse($twig, array(
            'profile' => $user,
            'minPassLength' => self::MIN_PASSWORD_LENGTH,
            'maxPassLength' => self::MAX_PASSWORD_LENGTH,
            'country' => $country,
            'firstMail' => $firstMail,
            'secondMail' => $secondMail,
            'username' => $username,
            'nolb_user' => $nolb_user,
        ));
    }

    /**
     * @Route("/emailEdit", name="email_edit")
     * @Method({"GET"})
     */
    public function EmailEditAction(Request $request){
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        $nolb_user = $user->getNolbUser();
        $twig = '::emailEdit.html.twig';
        return $this->get('templating')->renderResponse($twig, array(
            'nolb_user' => $nolb_user,
        ));
    }

    /**
     * @Route("/avatarEdit", name="avatar_edit")
     * @Method({"GET"})
     */
    public function AvatarEditAction(Request $request){
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        $nolb_user = $user->getNolbUser();
        $twig = '::avatarEdit.html.twig';
        return $this->get('templating')->renderResponse($twig, array(
            'nolb_user' => $nolb_user,
        ));
    }


    /**
     * @Route("/passwordEdit", name="password_edit")
     * @Method({"GET"})
     */
    public function passwordEditAction(Request $request){
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        $nolb_user = $user->getNolbUser();
        $twig = '::passwordEdit.html.twig';
        return $this->get('templating')->renderResponse($twig, array(
            'minPassLength' => self::MIN_PASSWORD_LENGTH,
            'maxPassLength' => self::MAX_PASSWORD_LENGTH,
            'nolb_user' => $nolb_user,
        ));
    }


    /**
     * @Route("/countryEdit", name="country_edit")
     * @Method({"GET"})
     */
    public function countryEditAction(Request $request){
        $user = $this->getUser();
        if (!$user) {
            return $this->redirectToRoute('fos_user_security_login');
        }
        $nolb_user = $user->getNolbUser();
        $twig = '::countryEdit.html.twig';
        return $this->get('templating')->renderResponse($twig, array(
            'nolb_user' => $nolb_user,
        ));
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

        $encoder_factory = $this->get('security.encoder_factory');

        $encoder = $encoder_factory->getEncoder($user);
        $salt = $user->getSalt();
        $old_password = $request->request->get('oldPassword');

        $encoded_password = $encoder->encodePassword($old_password, $salt);

        $logger = $this->get('logger');
        $logger->info('I just got the logger');
        $logger->info($user->getPassword() . " ---|--- " . $encoded_password);
        $logger->info('ENDE');

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
   * @Route("/deleteAccount", name="profile_delete_account")
   * @Method({"POST"})
   */
  public function deleteAccountAction(Request $request)
  {
    /*
     * @var $user \Catrobat\AppBundle\Entity\User
     */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('fos_user_security_login');
    }

    $em = $this->getDoctrine()->getManager();

    $user_id = $user->getId();
    $user_comments = $this->getDoctrine()
      ->getRepository('AppBundle:UserComment')
      ->findBy(array('userId' => $user_id), array('id' => 'DESC'));

    foreach ($user_comments as $comment) {
      $em->remove($comment);
    }

    $em->remove($user);
    $em->flush();

    return JsonResponse::create(array(
      'statusCode' => StatusCode::OK,
      'count' => count($user_comments)
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
      $countries = Intl::getRegionBundle()->getCountryNames($locale ?: $this->localeDetector->getLocale());
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
