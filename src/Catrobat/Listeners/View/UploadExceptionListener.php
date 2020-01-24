<?php

namespace App\Catrobat\Listeners\View;

use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class UploadExceptionListener
 * @package App\Catrobat\Listeners\View
 */
class UploadExceptionListener
{

  /**
   * @var TranslatorInterface
   */
  private $translator;

  /**
   * UploadExceptionListener constructor.
   *
   * @param TranslatorInterface $translator
   */
  public function __construct(TranslatorInterface $translator)
  {
    $this->translator = $translator;
  }

  /**
   * @param ExceptionEvent $event
   */
  public function onKernelException(ExceptionEvent $event)
  {
    if ($event->getThrowable() instanceof InvalidCatrobatFileException)
    {
      $event->allowCustomResponseCode();
      $event->setResponse(JsonResponse::create([
        "statusCode"        => $event->getThrowable()->getCode(),
        "answer"            => $this->translator->trans($event->getThrowable()->getMessage(), [], "catroweb"),
        "preHeaderMessages" => "",
      ], 200, [
        'X-Status-Code' => 200,
      ]));
    }
  }
}