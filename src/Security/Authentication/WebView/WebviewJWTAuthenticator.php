<?php

namespace App\Security\Authentication\WebView;

use App\Security\Authentication\CookieService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebviewJWTAuthenticator extends JWTTokenAuthenticator
{
  public function __construct(
        private readonly CookieService $cookie_service,
        JWTTokenManagerInterface $jwtManager,
        EventDispatcherInterface $dispatcher,
        TokenExtractorInterface $tokenExtractor,
        TokenStorageInterface $preAuthenticationTokenStorage,
        TranslatorInterface $translator = null)
  {
    parent::__construct($jwtManager, $dispatcher, $tokenExtractor, $preAuthenticationTokenStorage, $translator);
  }

  /**
   * @psalm-suppress ParamNameMismatch
   *
   * {@inheritDoc}
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $authException)
  {
    $response = parent::onAuthenticationFailure($request, $authException);

    if (Response::HTTP_UNAUTHORIZED === $response->getStatusCode() && !$request->headers->get('Authorization')) {
      $this->cookie_service->clearCookie('BEARER');
      // RefreshBearerCookieOnKernelResponse will try to create a new Bearer or is going to remove the refresh token!

      return new RedirectResponse($request->getBaseUrl());
    }

    return $response;
  }
}
