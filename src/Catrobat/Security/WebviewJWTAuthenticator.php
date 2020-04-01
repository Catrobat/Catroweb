<?php

namespace App\Catrobat\Security;

use Lexik\Bundle\JWTAuthenticationBundle\Security\Guard\JWTTokenAuthenticator;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\TokenExtractor\TokenExtractorInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Contracts\Translation\TranslatorInterface;

class WebviewJWTAuthenticator extends JWTTokenAuthenticator
{
  protected TranslatorInterface $translator;

  protected SessionInterface $session;

  public function __construct(
    JWTTokenManagerInterface $jwtManager,
    EventDispatcherInterface $dispatcher,
    TokenExtractorInterface $tokenExtractor,
    TranslatorInterface $translator, SessionInterface $session
  ) {
    parent::__construct($jwtManager, $dispatcher, $tokenExtractor);
    $this->translator = $translator;
    $this->session = $session;
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
   * @throws HttpException
   *
   * @return Response|void|null
   */
  public function onAuthenticationFailure(Request $request, AuthenticationException $authException)
  {
    throw new HttpException(Response::HTTP_UNAUTHORIZED, $this->translator->trans('errors.authentication.webview', [], 'catroweb'), null, [], 0);
  }
}
