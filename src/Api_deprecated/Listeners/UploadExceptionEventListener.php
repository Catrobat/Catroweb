<?php

declare(strict_types=1);

namespace App\Api_deprecated\Listeners;

use App\Project\CatrobatFile\InvalidCatrobatFileException;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
#[AsEventListener(event: KernelEvents::EXCEPTION, method: 'onKernelException')]
readonly class UploadExceptionEventListener
{
  public function __construct(private TranslatorInterface $translator)
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
}
