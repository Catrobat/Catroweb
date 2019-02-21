<?php

namespace Catrobat\AppBundle\Security;

use Catrobat\AppBundle\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Component\HttpFoundation\Response;
use Catrobat\AppBundle\StatusCode;
use Symfony\Component\Security\Http\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class ApiKeyAuthenticator
 * @package Catrobat\AppBundle\Security
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
   */
  public function createToken(Request $request, $providerKey)
  {
    $upload_token = $request->request->get('token');
    $username = $request->request->get('username');

    if (!$upload_token)
    {
      throw new BadCredentialsException('No API key found');
    }

    return new PreAuthenticatedToken($username, $upload_token, $providerKey);
  }

  /**
   * @param TokenInterface        $token
   * @param UserProviderInterface $userProvider
   * @param                       $providerKey
   *
   * @return PreAuthenticatedToken
   */
  public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
  {
    /**
     * @var $user User
     */
    $user = $userProvider->loadUserByUsername($token->getUsername());
    if (!$user)
    {
      throw new AuthenticationException('No user found');
    }

    if ($token->getCredentials() === $user->getUploadToken())
    {
      $authenticated_token = new PreAuthenticatedToken($user, $token->getCredentials(), $providerKey, $user->getRoles());
      $authenticated_token->setAuthenticated(true);

      return $authenticated_token;
    }
    else
    {
      throw new AuthenticationException('Upload Token auth failed.');
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
    return JsonResponse::create(['statusCode' => StatusCode::LOGIN_ERROR, 'answer' => $this->trans('errors.token'), 'preHeaderMessages' => ''], Response::HTTP_UNAUTHORIZED);
  }

  /**
   * @param       $message
   * @param array $parameters
   *
   * @return string
   */
  private function trans($message, $parameters = [])
  {
    return $this->translator->trans($message, $parameters, 'catroweb');
  }
}
