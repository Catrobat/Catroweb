<?php

namespace App\Application\Framework;

use App\Security\Authentication\CookieService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionListener
{
  protected LoggerInterface $logger;
  protected LoggerInterface $softLogger;
  protected TranslatorInterface $translator;
  protected ParameterBagInterface $parameter_bag;
  protected UrlGeneratorInterface $url_generator;

  public function __construct(LoggerInterface $logger, LoggerInterface $softLogger, TranslatorInterface $translator, ParameterBagInterface $parameter_bag, UrlGeneratorInterface $url_generator)
  {
    $this->logger = $logger;
    $this->softLogger = $softLogger;
    $this->translator = $translator;
    $this->parameter_bag = $parameter_bag;
    $this->url_generator = $url_generator;
  }

  public function onKernelException(ExceptionEvent $event): ?Response
  {
    $exception = $event->getThrowable();

    $themes = explode('|', strval($this->parameter_bag->get('themeRoutes')));
    $request = $event->getRequest();
    $theme = 'app';
    $applicationRequest = false;
    $requestUri = str_replace('/index_test.php', '', $request->getRequestUri());
    $requestUri = str_replace('/index.php', '', $requestUri);
    foreach ($themes as $theme) {
      if (str_starts_with($requestUri, '/'.$theme) && $this->isNoLegacyApiCall($requestUri, $theme)) {
        $applicationRequest = true;
        break;
      }
    }
    if (!$applicationRequest) {
      return $event->getResponse();
    }

    if ($exception instanceof NotFoundHttpException) {
      $this->softLogger->error('Http '.$exception->getStatusCode().': '.$exception->getMessage());
      /** @var Session $session */
      $session = $event->getRequest()->getSession();
      $session->getFlashBag()->add('snackbar', $this->translator->trans('doesNotExist', [], 'catroweb'));

      $event->setResponse(new RedirectResponse($this->url_generator->generate('index', ['theme' => $theme])));
    }

    if (Response::HTTP_UNAUTHORIZED === $exception->getCode()) {
      CookieService::clearCookie('CATRO_LOGIN_TOKEN');
      CookieService::clearCookie('BEARER');
      /** @var Session $session */
      $session = $event->getRequest()->getSession();
      $session->getFlashBag()->add('snackbar', $this->translator->trans('errors.authentication.webview', [], 'catroweb'));

      $event->setResponse(new RedirectResponse($this->url_generator->generate('login', ['theme' => $theme])));
    }

    return $event->getResponse();
  }

  protected function isNoLegacyApiCall(string $requestUri, string $theme): bool
  {
    return !str_starts_with($requestUri, '/'.$theme.'/api/') && !str_starts_with($requestUri, '/'.$theme.'/ci/') && !str_starts_with($requestUri, '/'.$theme.'/download/');
  }
}
