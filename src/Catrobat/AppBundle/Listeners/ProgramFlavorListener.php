<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Entity\Program;
use Catrobat\AppBundle\StatusCode;
use Symfony\Component\HttpFoundation\RequestStack;

class ProgramFlavorListener
{
  public function __construct(RequestStack $stack)
  {
    $this->request_stack = $stack;
  }

  public function onEvent(ProgramBeforePersistEvent $event)
  {
    $this->checkFlavor($event->getProgramEntity());
  }

  public function checkFlavor(Program $program)
  {
    $request = $this->request_stack->getCurrentRequest();
    if ($request == null)
    {
      $program->setFlavor('pocketcode');
    }
    else
    {
      $program->setFlavor($request->attributes->get('flavor'));
    }
  }
}
