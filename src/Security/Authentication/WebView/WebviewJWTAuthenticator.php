<?php

namespace App\Security\Authentication\WebView;

use App\Security\Authentication\CookieService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Authenticator\JWTAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/** @psalm-suppress UnimplementedInterfaceMethod */
class WebviewJWTAuthenticator extends JWTAuthenticator
{
  public function __construct(
    private readonly CookieService $cookie_service,
    JWTTokenManagerInterface $jwtManager,
    EventDispatcherInterface $dispatcher,
    TokenExtractorInterface $tokenExtractor,
    UserProviderInterface $userProvider,
    TranslatorInterface $translator = null)
  {
    parent::__construct($jwtManager, $dispatcher, $tokenExtractor, $userProvider, $translator);
  }

  /**
   * @psalm-suppress ParamNameMismatch
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
  {
    $response = parent::onAuthenticationFailure($request, $exception);

    if (Response::HTTP_UNAUTHORIZED === $response->getStatusCode() && !$request->headers->get('Authorization')) {
      $this->cookie_service->clearCookie('BEARER');
      // RefreshBearerCookieOnKernelResponse will try to create a new Bearer or is going to remove the refresh token!

      return new RedirectResponse(empty($request->getBaseUrl()) ? '/' : $request->getBaseUrl());
    }

    return $response;
  }
}
