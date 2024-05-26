<?php

declare(strict_types=1);

namespace App\Api_deprecated\Listeners;

use App\Project\CatrobatFile\InvalidCatrobatFileException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class UploadExceptionEventSubscriber implements EventSubscriberInterface
{
  public function __construct(private readonly TranslatorInterface $translator)
  {
  }

  public function onKernelException(ExceptionEvent $event): void
  {
    if ($event->getThrowable() instanceof InvalidCatrobatFileException) {
      $event->allowCustomResponseCode();
      $event->setResponse(new JsonResponse([
        'statusCode' => $event->getThrowable()->getCode(),
        'answer' => $this->translator->trans($event->getThrowable()->getMessage(), [], 'catroweb'),
        'preHeaderMessages' => '',
      ], Response::HTTP_OK, [
        'X-Status-Code' => 200,
      ]));
    }
  }

  #[\Override]
  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::EXCEPTION => 'onKernelException'];
  }
}
