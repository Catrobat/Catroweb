<?php

namespace App\Api_deprecated\Listeners;

use App\Project\CatrobatFile\InvalidCatrobatFileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class UploadExceptionListener
{
  private TranslatorInterface $translator;

  public function __construct(TranslatorInterface $translator)
  {
    $this->translator = $translator;
  }

  public function onKernelException(ExceptionEvent $event): void
  {
    if ($event->getThrowable() instanceof InvalidCatrobatFileException) {
      $event->allowCustomResponseCode();
      $event->setResponse(new JsonResponse([
        'statusCode' => $event->getThrowable()->getCode(),
        'answer' => $this->translator->trans($event->getThrowable()->getMessage(), [], 'catroweb'),
        'preHeaderMessages' => '',
      ], 200, [
        'X-Status-Code' => 200,
      ]));
    }
  }
}
