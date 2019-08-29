<?php

namespace App\Catrobat\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class WebviewAuthenticator
 * @package App\Catrobat\Security
 */
class WebviewAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
  /**
   * @var TranslatorInterface
   */
  protected $translator;

  /** @var SessionInterface */
  protected $session;

  /**
   * WebviewAuthenticator constructor.
   *
   * @param TranslatorInterface $translator
   * @param SessionInterface    $session
   */
  public function __construct(TranslatorInterface $translator, SessionInterface $session)
  {
    $this->translator = $translator;
    $this->session = $session;
  }

  protected $cookie_name_user = "CATRO_LOGIN_USER";
  protected $cookie_name_token = "CATRO_LOGIN_TOKEN";


  /**
   * @param Request $request
   * @param         $providerKey
   *
   * @return PreAuthenticatedToken
   */
  public function createToken(Request $request, $providerKey)
  {
    $user = $request->cookies->get($this->cookie_name_user, null);
    $token = $request->cookies->get($this->cookie_name_token, null);
    $this->session->set('webview-auth', false);

    if (!$user || !$token)
    {
      return null;
    }
    else
    {
      return new PreAuthenticatedToken($user, $token, $providerKey);
    }
  }

  /**
   * @param TokenInterface        $token
   * @param UserProviderInterface $userProvider
   * @param                       $providerKey
   *
   * @return PreAuthenticatedToken
   * @throws AuthenticationException
   */
  public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
  {
    /**
     * @var $user User
     */
    try
    {
      $user = $userProvider->loadUserByUsername($token->getUsername());
    } catch (UsernameNotFoundException $exception)
    {
      throw new AuthenticationException(
        $this->translator->trans("errors.authentication.webview", [], "catroweb")
      );
    }

    if ($user && $token && $token->getCredentials() === $user->getUploadToken())
    {
      $authenticated_token = new PreAuthenticatedToken(
        $user, $token->getCredentials(), $providerKey, $user->getRoles()
      );
      $authenticated_token->setAuthenticated(true);
      $this->session->set('webview-auth', true);

      return $authenticated_token;
    }
    else
    {
      throw new AuthenticationException(
        $this->translator->trans("errors.authentication.webview", [], "catroweb")
      );
    }
  }

  /**
   * @param TokenInterface $token
   * @param                $providerKey
   *
   * @return bool
   */
  public function supportsToken(TokenInterface $token, $providerKey)
  {
    return $token instanceof PreAuthenticatedToken && $token->getProviderKey() === $providerKey;
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
}
