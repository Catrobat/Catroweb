<?php

namespace App\Catrobat\Listeners\View;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;
use Symfony\Component\Translation\TranslatorInterface;

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
   * @param GetResponseForExceptionEvent $event
   */
  public function onKernelException(GetResponseForExceptionEvent $event)
  {
    if ($event->getException() instanceof InvalidCatrobatFileException)
    {
      $event->allowCustomResponseCode();
      $event->setResponse(JsonResponse::create([
        "statusCode"        => $event->getException()->getCode(),
        "answer"            => $this->translator->trans($event->getException()->getMessage(), [], "catroweb"),
        "preHeaderMessages" => "",
      ], 200, [
        'X-Status-Code' => 200,
      ]));
    }
  }
}