<?php


namespace App\Catrobat\Security;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class WebviewAuthenticator
 * @package App\Catrobat\Security
 */
class WebviewAuthenticator extends AbstractGuardAuthenticator
{
  /**
   * @required request cookie CATRO_LOGIN_TOKEN to automatically log in a user in the webview
   *
   *  Must be sent as cookie containing the user token
   *  Must not be empty
   *
   */
  const COOKIE_TOKEN_KEY = 'CATRO_LOGIN_TOKEN';

  /**
   * @var TranslatorInterface
   */
  protected $translator;

  /**
   * @var SessionInterface
   */
  protected $session;

  /**
   * @var EntityManagerInterface
   */
  private $em;

  /**
   * WebviewAuthenticator constructor.
   *
   * @param EntityManagerInterface $em
   * @param TranslatorInterface    $translator
   * @param SessionInterface       $session
   */
  public function __construct(EntityManagerInterface $em, TranslatorInterface $translator, SessionInterface $session)
  {
    $this->em = $em;
    $this->translator = $translator;
    $this->session = $session;
  }

  /**
   * Called on every request to decide if this authenticator should be
   * used for the request. Returning false will cause this authenticator
   * to be skipped.
   *
   * @param Request $request
   *
   * @return bool
   */
  public function supports(Request $request)
  {
    $this->session->set('webview-auth', false);

    return $this->hasValidTokenCookieSet($request);
  }

  /**
   * Called on every request. Return whatever credentials you want to
   * be passed to getUser() as $credentials.
   *
   * @param Request $request
   *
   * @return array|mixed
   */
  public function getCredentials(Request $request)
  {
    return [
      self::COOKIE_TOKEN_KEY => $request->cookies->get(self::COOKIE_TOKEN_KEY, null),
    ];
  }

  /**
   * @param mixed                 $credentials
   * @param UserProviderInterface $userProvider
   *
   * @return User|null
   */
  public function getUser($credentials, UserProviderInterface $userProvider)
  {
    $token = $credentials[self::COOKIE_TOKEN_KEY];

    if (null === $token || "" === $token)
    {
      throw new AuthenticationException(
        $this->translator->trans("errors.authentication.webview", [], "catroweb")
      );
    }

    $user = $this->em->getRepository(User::class)
      ->findOneBy(['upload_token' => $token]);

    if (!$user)
    {
      throw new AuthenticationException(
        $this->translator->trans("errors.authentication.webview", [], "catroweb")
      );
    }

    // if a User object, checkCredentials() is called
    return $user;
  }

  /**
   *  Called to make sure the credentials are valid
   *    - E.g mail, username, or password
   *    - no additional checks are also valid
   *
   * @param mixed         $credentials
   * @param UserInterface $user
   *
   * @return bool
   */
  public function checkCredentials($credentials, UserInterface $user)
  {
    // return true to cause authentication success
    return true;
  }

  /**
   * @param Request        $request
   * @param TokenInterface $token
   * @param string         $providerKey
   *
   * @return Response|null
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
  {
    $this->session->set('webview-auth', true);

    // on success, let the request continue
    return null;
  }

  /**
   * @param Request                 $request
   * @param AuthenticationException $exception
   *
   * @throws HttpException
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
  {
    throw new HttpException(Response::HTTP_UNAUTHORIZED, $exception->getMessage(), null, [], 0);
  }


  /**
   * @param Request                      $request
   * @param AuthenticationException|null $authException
   *
   * @return Response|void
   */
  public function start(Request $request, AuthenticationException $authException = null)
  {
    throw new AuthenticationException(
      $this->translator->trans("errors.authentication.webview", [], "catroweb")
    );
  }

  /**
   * @return bool
   */
  public function supportsRememberMe()
  {
    return false;
  }

  /**
   * @param Request $request
   *
   * @return bool
   */
  private function hasValidTokenCookieSet(Request $request)
  {
    return $request->cookies->has(self::COOKIE_TOKEN_KEY) && "" !== $request->cookies->get(self::COOKIE_TOKEN_KEY);
  }

}
