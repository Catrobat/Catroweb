<?php

namespace App\Security\Authentication\WebView;

use App\Security\Authentication\CookieService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

class WebviewJWTAuthenticator extends JWTTokenAuthenticator
{
  /**
   * @psalm-suppress ParamNameMismatch
   *
   * {@inheritDoc}
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $authException)
  {
    if ($request->cookies->has('REFRESH_TOKEN')) {
      CookieService::clearCookie('BEARER');
      // RefreshBearerCookieOnKernelResponse will try to create a new Bearer or is going to remove the refresh token!
      exit();
    }

    if ($request->headers->get('Authorization')) {
      return null;
    }

    // "caught" in ErrorController
    throw new HttpException(Response::HTTP_UNAUTHORIZED, 'jwt authentication failed', null, [], Response::HTTP_UNAUTHORIZED);
  }
}
