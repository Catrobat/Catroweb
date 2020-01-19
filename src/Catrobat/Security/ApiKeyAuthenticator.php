<?php

namespace App\Catrobat\Security;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use App\Catrobat\StatusCode;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ApiKeyAuthenticator
 * @package App\Catrobat\Security
 */
class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
  /**
   * @var TranslatorInterface
   */
  protected $translator;

  /**
   * ApiKeyAuthenticator constructor.
   *
   * @param TranslatorInterface $translator
   */
  public function __construct(TranslatorInterface $translator)
  {
    $this->translator = $translator;
  }

  /**
   * @param Request $request
   * @param         $providerKey
   *
   * @return PreAuthenticatedToken
   * @throws BadCredentialsException
   */
  public function createToken(Request $request, $providerKey)
  {
    $credentials = $request->request->get('token');
    $username = $request->request->get('username');

    if (!$credentials)
    {
      $credentials = null;
    }

    if (!$username)
    {
      $username = "";
    }

    return new PreAuthenticatedToken($username, $credentials, $providerKey);
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

    if (!$token->getCredentials())
    {
      throw new AuthenticationException(
        $this->translator->trans("errors.token", [], 'catroweb'));
    }

    if (!$token->getUsername())
    {
      throw new AuthenticationException(
        $this->translator->trans("errors.username.blank", [], 'catroweb'));
    }

    try
    {
      $user = $userProvider->loadUserByUsername($token->getUsername());
    } catch (UsernameNotFoundException $exception)
    {
      throw new AuthenticationException(
        $this->translator->trans("errors.username.not_exists", [], 'catroweb'));
    }

    if ($token->getCredentials() === $user->getUploadToken())
    {
      $authenticated_token = new PreAuthenticatedToken(
        $user, $token->getCredentials(), $providerKey, $user->getRoles()
      );
      $authenticated_token->setAuthenticated(true);

      return $authenticated_token;
    }
    else
    {
      throw new AuthenticationException(
        $this->translator->trans("errors.uploadTokenAuthFailed", [], 'catroweb'));
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
   * @return JsonResponse|Response
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
  {
    return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR,
                                 'answer'     => $exception->getMessage(), 'preHeaderMessages' => ""],
      Response::HTTP_UNAUTHORIZED);
  }
}
