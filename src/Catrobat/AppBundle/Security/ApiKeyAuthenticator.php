<?php

namespace Catrobat\AppBundle\Security;

use Symfony\Component\Security\Core\Authentication\SimplePreAuthenticatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\Security\Core\Authentication\Token\PreAuthenticatedToken;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use FOS\UserBundle\Security\UserProvider;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Authentication\AuthenticationFailureHandlerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpFoundation\Response;
use Catrobat\AppBundle\StatusCode;

class ApiKeyAuthenticator implements SimplePreAuthenticatorInterface, AuthenticationFailureHandlerInterface
{
  protected $user_provider;
  
  public function __construct(UserProvider $user_provider, Translator $translator)
  {
    $this->user_provider = $user_provider;
    $this->translator = $translator;
  }
  
  public function createToken(Request $request, $providerKey)
  {
    $upload_token = $request->request->get ( 'token' );
    $username = $request->request->get ( 'username' );
    
    if (!$upload_token)
    {
      throw new BadCredentialsException ('No API key found');
    }
    
    return new PreAuthenticatedToken ( $username, $upload_token, $providerKey );
  }
  
  public function authenticateToken(TokenInterface $token, UserProviderInterface $userProvider, $providerKey)
  {
    $user = $this->user_provider->loadUserByUsername($token->getUsername());
    if (!$user)
    {
      throw new AuthenticationException ('No user found');
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
  
  public function supportsToken(TokenInterface $token, $providerKey)
  {
    return $token instanceof PreAuthenticatedToken && $token->getProviderKey () === $providerKey;
  }
  
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception)
  {
    return JsonResponse::create(array("statusCode" => StatusCode::LOGIN_ERROR, "answer" => $this->trans("error.token"), "preHeaderMessages" => ""),Response::HTTP_UNAUTHORIZED);
  }
  
  private function trans($message, $parameters = array())
  {
    return $this->translator->trans($message,$parameters,"catroweb_api");
  }
  
  
  
}