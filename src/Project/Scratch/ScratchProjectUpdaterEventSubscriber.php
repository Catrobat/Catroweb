<?php

namespace App\Project\Scratch;

use App\Project\Event\CheckScratchProgramEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ScratchProjectUpdaterEventSubscriber implements EventSubscriberInterface
{
  public function __construct(protected ScratchManager $scratch_manager)
  {
  }

  public function onCheckScratchProgram(CheckScratchProgramEvent $event): void
  {
    $this->scratch_manager->createScratchProgramFromId($event->getScratchId());
  }

  public static function getSubscribedEvents(): array
  {
    return [CheckScratchProgramEvent::class => 'onCheckScratchProgram'];
  }
}
