<?php

declare(strict_types=1);

namespace App\Application\Framework;

use App\Security\Authentication\CookieService;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ExceptionEventSubscriber implements EventSubscriberInterface
{
  public function __construct(
    protected LoggerInterface $logger,
    protected LoggerInterface $softLogger,
    protected TranslatorInterface $translator,
    protected ParameterBagInterface $parameter_bag,
    protected UrlGeneratorInterface $url_generator,
    protected CookieService $cookie_service
  ) {
  }

  public function onKernelException(ExceptionEvent $event): ?Response
  {
    $exception = $event->getThrowable();
    $request = $event->getRequest();

    // check request
    $themes = explode('|', (string) $this->parameter_bag->get('themeRoutes'));
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

    // 401, 403 - redirect to login page
    if ($exception instanceof HttpException && (Response::HTTP_UNAUTHORIZED === $exception->getStatusCode() || Response::HTTP_FORBIDDEN === $exception->getStatusCode())) {
      $this->cookie_service->clearCookie('CATRO_LOGIN_TOKEN');
      $this->cookie_service->clearCookie('BEARER');
      /** @var Session $session */
      $session = $event->getRequest()->getSession();
      $session->getFlashBag()->add('snackbar', $this->translator->trans('errors.authentication.webview', [], 'catroweb'));

      $event->setResponse(new RedirectResponse($this->url_generator->generate('login', ['theme' => $theme])));
    }

    // 404 - redirect to index page
    if ($exception instanceof HttpException && Response::HTTP_NOT_FOUND === $exception->getStatusCode()) {
      $this->softLogger->error('Http '.$exception->getStatusCode().': '.$exception->getMessage());
      /** @var Session $session */
      $session = $event->getRequest()->getSession();
      $session->getFlashBag()->add('snackbar', $this->translator->trans('doesNotExist', [], 'catroweb'));

      $event->setResponse(new RedirectResponse($this->url_generator->generate('index', ['theme' => $theme])));
    }

    return $event->getResponse();
  }

  protected function isNoLegacyApiCall(string $requestUri, string $theme): bool
  {
    return !str_starts_with($requestUri, '/'.$theme.'/api/') && !str_starts_with($requestUri, '/'.$theme.'/ci/') && !str_starts_with($requestUri, '/'.$theme.'/download/');
  }

  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::EXCEPTION => 'onKernelException'];
  }
}
