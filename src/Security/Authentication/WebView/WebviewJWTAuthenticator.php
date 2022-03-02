<?php

namespace App\Security\Authentication\WebView;

use App\Security\Authentication\CookieService;
use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebviewJWTAuthenticator extends JWTTokenAuthenticator
{
  protected TranslatorInterface $translator;
  protected SessionInterface $session;
  protected LoggerInterface $logger;
  protected UrlGeneratorInterface $url_generator;

  public function __construct(
    JWTTokenManagerInterface $jwtManager,
    EventDispatcherInterface $dispatcher,
    TokenExtractorInterface $tokenExtractor,
    TokenStorageInterface $tokenStorage,
    TranslatorInterface $translator,
    SessionInterface $session,
    LoggerInterface $logger,
    UrlGeneratorInterface $url_generator
  ) {
    parent::__construct($jwtManager, $dispatcher, $tokenExtractor, $tokenStorage);
    $this->translator = $translator;
    $this->session = $session;
    $this->logger = $logger;
    $this->url_generator = $url_generator;
  }

  /**
   * @return bool
   */
  public function supports(Request $request)
  {
    $this->session->set('webview-auth', false);

    return parent::supports($request);
  }

  /**
   * @param string $providerKey
   *
   * @return Response|null
   */
  public function onAuthenticationSuccess(Request $request, TokenInterface $token, $providerKey)
  {
    $this->session->set('webview-auth', true);

    return parent::onAuthenticationSuccess($request, $token, $providerKey);
  }

  /**
   * @psalm-suppress ParamNameMismatch
   *
   * {@inheritDoc}
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $authException)
  {
    $this->logger->warning('WebviewJWT Authentication failed: '.$authException->getMessage());
    CookieService::clearCookie('LOGGED_IN');
    CookieService::clearCookie('BEARER');
    CookieService::clearCookie('REFRESH_TOKEN');
    $request->getSession()->invalidate();
    if ($request->headers->get('Authorization')) {
      return null;
    }

    return new RedirectResponse($this->url_generator->generate('login', ['theme' => 'app']));
  }
}
