<?php

namespace Catrobat\AppBundle\Listeners;

use Catrobat\AppBundle\Events\ProgramBeforePersistEvent;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ProgramFlavorListener
 * @package Catrobat\AppBundle\Listeners
 */
class ProgramFlavorListener
{

  /**
   * @var RequestStack
   */
  private $request_stack;

  /**
   * ProgramFlavorListener constructor.
   *
   * @param RequestStack $stack
   */
  public function __construct(RequestStack $stack)
  {
    $this->request_stack = $stack;
  }

  /**
   * @param ProgramBeforePersistEvent $event
   */
  public function onEvent(ProgramBeforePersistEvent $event)
  {
    $this->checkFlavor($event->getProgramEntity());
  }

  /**
   * @param Program $program
   */
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
