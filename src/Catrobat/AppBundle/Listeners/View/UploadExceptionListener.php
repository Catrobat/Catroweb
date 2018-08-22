<?php
namespace Catrobat\AppBundle\Listeners\View;

use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Symfony\Component\Translation\TranslatorInterface;

class UploadExceptionListener
{

    private $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if ($event->getException() instanceof InvalidCatrobatFileException) {
            $event->allowCustomResponseCode();
            $event->setResponse(JsonResponse::create(array(
                "statusCode" => $event->getException()
                    ->getStatusCode(),
                "answer" => $this->translator->trans($event->getException()
                    ->getMessage(), array(), "catroweb"),
                "preHeaderMessages" => ""
            ), 200,  array(
                'X-Status-Code' => 200
            )));
        }
    }
}