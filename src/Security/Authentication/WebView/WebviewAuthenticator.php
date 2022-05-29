<?php

namespace App\Security\Authentication\WebView;

use App\DB\Entity\User\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Guard\AbstractGuardAuthenticator;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class WebviewAuthenticator.
 *
 * @deprecated
 */
class WebviewAuthenticator extends AbstractGuardAuthenticator
{
  /**
   * @required request cookie CATRO_LOGIN_TOKEN to automatically log in a user in the webview
   *
   *  Must be sent as cookie containing the user token
   *  Must not be empty
   *
   * @var string
   */
  final public const COOKIE_TOKEN_KEY = 'CATRO_LOGIN_TOKEN';

  public function __construct(
      private readonly EntityManagerInterface $em,
      protected TranslatorInterface $translator,
      protected RequestStack $request_stack,
      protected LoggerInterface $logger,
      protected UrlGeneratorInterface $url_generator
  ) {
  }

  /**
   * Called on every request to decide if this authenticator should be
   * used for the request. Returning false will cause this authenticator
   * to be skipped.
   *
   * {@inheritdoc}
   */
  public function supports(Request $request)
  {
    $this->request_stack->getSession()->set('webview-auth', false);

    return $this->hasValidTokenCookieSet($request);
  }

  /**
   * Called on every request. Return whatever credentials you want to
   * be passed to getUser() as $credentials.
   *
   * {@inheritdoc}
   */
  public function getCredentials(Request $request)
  {
    return [
      self::COOKIE_TOKEN_KEY => $request->cookies->get(self::COOKIE_TOKEN_KEY),
    ];
  }

  /**
   * @param mixed $credentials
   *
   * {@inheritdoc}
   */
  public function getUser($credentials, UserProviderInterface $userProvider)
  {
    $token = $credentials[self::COOKIE_TOKEN_KEY];

    if (null === $token || '' === $token) {
      throw new AuthenticationException('Empty token!');
    }

    $user = $this->em->getRepository(User::class)
      ->findOneBy(['upload_token' => $token])
    ;

    if (null === $user) {
      throw new AuthenticationException('User not found!');
    }

    // if a User object, checkCredentials() is called
    return $user;
  }

  /**
   *  Called to make sure the credentials are valid
   *    - E.g mail, username, or password
   *    - no additional checks are also valid.
   *
   * @param mixed $credentials
   *
   * {@inheritdoc}
   */
  public function checkCredentials($credentials, UserInterface $user)
  {
    // return true to cause authentication success
    return true;
  }

  /**
   * @param string $providerKey
   *
   * {@inheritdoc}
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $providerKey)
  {
    $this->request_stack->getSession()->set('webview-auth', true);

    // on success, let the request continue
    return null;
  }

  /**
   * @throws HttpException
   *
   * {@inheritdoc}
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
  {
    throw new HttpException(Response::HTTP_UNAUTHORIZED, $exception->getMessage(), null, [], Response::HTTP_UNAUTHORIZED);
  }

  /**
   * @throws AuthenticationException
   *
   * {@inheritDoc}
   */
  public function start(Request $request, AuthenticationException $authException = null): Response
  {
    throw new AuthenticationException($authException->getMessage());
  }

  /**
   * {@inheritDoc}
   */
  public function supportsRememberMe()
  {
    return false;
  }

  /**
   * @return bool
   */
  private function hasValidTokenCookieSet(Request $request)
  {
    return $request->cookies->has(self::COOKIE_TOKEN_KEY) && '' !== $request->cookies->get(self::COOKIE_TOKEN_KEY);
  }
}
