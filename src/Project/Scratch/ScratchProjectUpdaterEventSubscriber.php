<?php

declare(strict_types=1);

namespace App\Project\Scratch;

use App\Project\Event\CheckScratchProjectEvent;
use Doctrine\ORM\Exception\ORMException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ScratchProjectUpdaterEventSubscriber implements EventSubscriberInterface
{
  public function __construct(protected ScratchManager $scratch_manager)
  {
  }

  /**
   * @throws \Exception|ORMException
   */
  public function onCheckScratchProgram(CheckScratchProjectEvent $event): void
  {
    $this->scratch_manager->createScratchProjectFromId($event->getScratchId());
  }

  #[\Override]
  public static function getSubscribedEvents(): array
  {
    return [CheckScratchProjectEvent::class => 'onCheckScratchProgram'];
  }
}
