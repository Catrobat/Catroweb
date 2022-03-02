<?php

namespace App\Application\Controller\User;

use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Comment\UserCommentRepository;
use App\Project\ProgramManager;
use App\Security\Authentication\CookieService;
use App\Security\Authentication\RefreshTokenService;
use App\User\Achievements\AchievementManager;
use App\User\Notification\NotificationManager;
use App\User\UserManager;
use App\Utils\ImageUtils;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Criteria;
use Exception;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class ProfileController extends AbstractController
{
  public const MIN_PASSWORD_LENGTH = 6;
  public const MAX_PASSWORD_LENGTH = 4096;
  protected ProgramManager $program_manager;
  protected UserManager $user_manager;
  protected AchievementManager $achievement_manager;
  private JWTTokenManagerInterface $jwt_manager;
  private CookieService $cookie_service;
  private RefreshTokenService $token_manager;

  public function __construct(ProgramManager $program_manager, UserManager $user_manager,
                              AchievementManager $achievement_manager, JWTTokenManagerInterface $jwt_manager,
                              CookieService $cookie_service, RefreshTokenService $token_manager)
  {
    $this->program_manager = $program_manager;
    $this->user_manager = $user_manager;
    $this->achievement_manager = $achievement_manager;
    $this->jwt_manager = $jwt_manager;
    $this->cookie_service = $cookie_service;
    $this->token_manager = $token_manager;
  }

  /**
   * @Route("/user/{id}", name="profile", defaults={"id": "0"}, methods={"GET"})
   *
   * Overwrite for FosUser Profile Route (We don't use it!)
   * @Route("/user/}")
   */
  public function profileAction(Request $request, string $id): Response
  {
    /** @var User|null $user */
    $user = null;

    if ('0' === $id || ($this->getUser() && $this->getUser()->getId() === $id)) {
      $user = $this->getUser();
      if (is_null($user)) {
        return $this->redirectToRoute('login');
      }
      $program_count = $this->program_manager->countUserProjects($id);
      $view = 'UserManagement/Profile/myProfile.html.twig';
    } else {
      $user = $this->user_manager->find($id);
      if (is_null($user)) {
        return $this->redirectToRoute('index');
      }
      $program_count = $this->program_manager->countPublicUserProjects($id);
      $view = 'UserManagement/Profile/profile.html.twig';
    }

    return $this->render($view, [
      'profile' => $user,
      'program_count' => $program_count,
      'firstMail' => $user->getEmail(),
      'oauth_user' => $user->getGplusUid(),
      'minPassLength' => self::MIN_PASSWORD_LENGTH,
      'maxPassLength' => self::MAX_PASSWORD_LENGTH,
      'username' => $user->getUsername(),
      'followers_list' => $this->user_manager->getMappedUserData($user->getFollowers()->toArray()),
      'following_list' => $this->user_manager->getMappedUserData($user->getFollowing()->toArray()),
      'achievements' => $this->achievement_manager->findUnlockedAchievements($user),
    ]);
  }

  /**
   * @Route("/passwordSave", name="password_save", methods={"POST"})
   */
  public function passwordSaveAction(Request $request, UserManager $user_manager, EncoderFactoryInterface $factory): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('login');
    }

    $old_password = $request->request->get('oldPassword');

    $encoder = $factory->getEncoder($user);
    $bool = true;
    if (null !== $old_password) {
      $bool = $encoder->isPasswordValid($user->getPassword(), $old_password, $user->getSalt());
    }

    if (!$bool && ($user->isOauthPasswordCreated() || !$user->isOauthUser())) {
      return JsonResponse::create([
        'statusCode' => Response::HTTP_UNAUTHORIZED,
      ]);
    }

    $newPassword = $request->request->get('newPassword');
    $repeatPassword = $request->request->get('repeatPassword');

    try {
      $this->validateUserPassword($newPassword, $repeatPassword);
    } catch (Exception $exception) {
      return JsonResponse::create([
        'statusCode' => $exception->getCode(),
      ]);
    }

    if ('' !== $newPassword) {
      $user->setPlainPassword($newPassword);
    }
    if ($user->isOauthUser()) {
      $user->setOauthPasswordCreated(true);
    }
    $user_manager->updateUser($user);

    return JsonResponse::create([
      'statusCode' => Response::HTTP_OK,
      'saved_password' => 'supertoll',
    ]);
  }

  /**
   * @Route("/emailSave", name="email_save", methods={"POST"})
   */
  public function emailSaveAction(Request $request, UserManager $user_manager): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('login');
    }

    $firstMail = $request->request->get('firstEmail');

    if ('' === $firstMail) {
      return JsonResponse::create(['statusCode' => 808]);
    }

    try {
      $this->validateEmail($firstMail);
    } catch (Exception $exception) {
      return JsonResponse::create(['statusCode' => $exception->getCode(), 'email' => 1]);
    }

    if ($this->checkEmailExists($firstMail, $user_manager)) {
      return JsonResponse::create(['statusCode' => 810, 'email' => 1]);
    }

    if ($firstMail !== $user->getEmail()) {
      $user->setEmail($firstMail);
    }
    $user_manager->updateUser($user);

    return JsonResponse::create([
      'statusCode' => Response::HTTP_OK,
    ]);
  }

  /**
   * @Route("/usernameSave", name="username_save", methods={"POST"})
   */
  public function usernameSaveAction(Request $request, UserManager $user_manager): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('login');
    }

    $username = $request->request->get('username');

    if (null === $username || '' === $username) {
      return JsonResponse::create(['statusCode' => 811]);
    }

    try {
      $this->validateUsername($username);
    } catch (Exception $exception) {
      return JsonResponse::create(['statusCode' => 804]);
    }

    if ($this->checkUsernameExists($username, $user_manager)) {
      return JsonResponse::create(['statusCode' => 812]);
    }
    if (filter_var(str_replace(' ', '', $username), FILTER_VALIDATE_EMAIL)) {
      return JsonResponse::create(['statusCode' => 809]);
    }

    $user->setUsername($username);
    $user_manager->updateUser($user);

    $response = JsonResponse::create([
      'statusCode' => Response::HTTP_OK,
    ]);
    $response->headers->setCookie($this->cookie_service->createBearerTokenCookie($this->jwt_manager->create($user)));
    $this->token_manager->invalidateRefreshTokenOfUser($user->getUsername());
    $refreshToken = $this->token_manager->createRefreshTokenForUsername($user->getUsername());
    $response->headers->setCookie($this->cookie_service->createRefreshTokenCookie($refreshToken->getRefreshToken()));

    return $response;
  }

  /**
   * @Route("/userUploadAvatar", name="profile_upload_avatar", methods={"POST"})
   */
  public function uploadAvatarAction(Request $request, UserManager $user_manager): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (!$user) {
      return $this->redirectToRoute('login');
    }

    $image_base64 = $request->request->get('image');

    try {
      $image_base64 = ImageUtils::checkAndResizeBase64Image($image_base64);
    } catch (Exception $exception) {
      return JsonResponse::create(['statusCode' => $exception->getCode()]);
    }

    $user->setAvatar($image_base64);
    $user_manager->updateUser($user);

    return JsonResponse::create([
      'statusCode' => Response::HTTP_OK,
      'image_base64' => $image_base64,
    ]);
  }

  /**
   * @Route("/deleteAccount", name="profile_delete_account", methods={"POST"})
   */
  public function deleteAccountAction(UserCommentRepository $comment_repository): Response
  {
    /** @var User|null $user */
    $user = $this->getUser();

    if (!$user) {
      return $this->redirectToRoute('login');
    }

    $user_comments = $comment_repository->getCommentsWrittenByUser($user);

    $em = $this->getDoctrine()->getManager();
    $em->remove($user);
    $em->flush();

    return JsonResponse::create([
      'statusCode' => Response::HTTP_OK,
      'count' => count($user_comments),
    ]);
  }

  /**
   * @Route("/followUser/{id}", name="follow_user", methods={"GET"}, defaults={"id": "0"})
   */
  public function followUser(string $id, UserManager $user_manager, NotificationManager $notification_service): RedirectResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('login');
    }

    if ('0' === $id || $id === $user->getId()) {
      return $this->redirectToRoute('profile');
    }

    /** @var User $user_to_follow */
    $user_to_follow = $user_manager->find($id);
    $user->addFollowing($user_to_follow);
    $user_manager->updateUser($user);

    $notification = new FollowNotification($user_to_follow, $user);
    $notification_service->addNotification($notification);

    return $this->redirectToRoute('profile', ['id' => $id]);
  }

  /**
   * @Route("/unfollowUser/{id}", name="unfollow_user", methods={"GET"}, defaults={"id": "0"})
   */
  public function unfollowUser(string $id, UserManager $user_manager): RedirectResponse
  {
    /** @var User|null $user */
    $user = $this->getUser();
    if (null === $user) {
      return $this->redirectToRoute('login');
    }

    if ('0' === $id) {
      return $this->redirectToRoute('profile');
    }

    /** @var User $user_to_unfollow */
    $user_to_unfollow = $user_manager->find($id);
    $user->removeFollowing($user_to_unfollow);
    $user_manager->updateUser($user);

    return $this->redirectToRoute('profile', ['id' => $id]);
  }

  /**
   * @Route("/follow/{type}", name="list_follow", methods={"POST"}, defaults={"_format": "json"},
   * requirements={"type": "follower|follows"})
   */
  public function listFollow(Request $request, string $type, UserManager $user_manager): JsonResponse
  {
    $followCollection = null;
    $criteria = Criteria::create()
      ->orderBy(['username' => Criteria::ASC])
      ->setFirstResult($request->get('page') * $request->get('pageSize'))
      ->setMaxResults($request->get('pageSize'))
    ;

    /** @var User|null $user */
    $user = $user_manager->find($request->get('id'));

    switch ($type) {
      case 'follower':
        /** @var ArrayCollection $followCollection */
        $followCollection = $user->getFollowers();
        break;
      case 'follows':
        /** @var ArrayCollection $followCollection */
        $followCollection = $user->getFollowing();
        break;
    }
    $length = $followCollection->count();
    $followCollection->first();
    $users = $followCollection->matching($criteria)->toArray();

    $data = [];
    foreach ($users as $user) {
      $data[] = [
        'username' => $user->getUsername(),
        'id' => $user->getId(),
        'avatar' => $user->getAvatar(),
      ];
    }

    return JsonResponse::create(['profiles' => $data, 'maximum' => $length]);
  }

  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
  //// private functions
  //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

  /**
   * @throws Exception
   */
  private function validateUserPassword(string $pass1, string $pass2): void
  {
    if ($pass1 !== $pass2) {
      throw new Exception('USER_PASSWORD_NOT_EQUAL_PASSWORD2', 802);
    }

    if (0 === strcasecmp($this->getUser()->getUsername(), $pass1)) {
      throw new Exception('USER_USERNAME_PASSWORD_EQUAL', 813);
    }

    if ('' !== $pass1 && strlen($pass1) < self::MIN_PASSWORD_LENGTH) {
      throw new Exception('USER_PASSWORD_TOO_SHORT', 753);
    }

    if ('' !== $pass1 && strlen($pass1) > self::MAX_PASSWORD_LENGTH) {
      throw new Exception('USER_PASSWORD_TOO_LONG', 806);
    }
  }

  /**
   * @throws Exception
   */
  private function validateEmail(string $email): void
  {
    $name = '[a-zA-Z0-9]((\.|\-|_)?[a-zA-Z0-9])*';
    $domain = '[a-zA-Z]((\.|\-)?[a-zA-Z0-9])*';
    $tld = '[a-zA-Z]{2,8}';
    $regEx = '/^('.$name.')@('.$domain.')\.('.$tld.')$/';

    if (!preg_match($regEx, $email) && !empty($email)) {
      throw new Exception('USER_EMAIL_INVALID', 765);
    }
  }

  /**
   * @throws Exception
   */
  private function validateUsername(string $username): void
  {
    // also take a look at /config/validator/validation.xml when applying changes!
    if (strlen($username) < 3 || strlen($username) > 180) {
      throw new Exception('USERNAME_INVALID', 804);
    }
  }

  private function checkEmailExists(string $email, UserManager $user_manager): bool
  {
    if ('' === $email) {
      return false;
    }

    $userWithFirstMail = $user_manager->findOneBy(['email' => $email]);

    return null !== $userWithFirstMail && $userWithFirstMail !== $this->getUser();
  }

  private function checkUsernameExists(string $username, UserManager $user_manager): bool
  {
    if ('' === $username) {
      return false;
    }

    $user = $user_manager->findOneBy(['username' => $username]);

    return null !== $user && $user !== $this->getUser();
  }
}
