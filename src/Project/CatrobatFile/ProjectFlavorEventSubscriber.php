<?php

namespace App\Project\CatrobatFile;

use App\DB\Entity\Project\Project;
use App\Project\Event\ProjectBeforePersistEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RequestStack;

class ProjectFlavorEventSubscriber implements EventSubscriberInterface
{
  public function __construct(private readonly RequestStack $request_stack)
  {
  }

  public function onEvent(ProjectBeforePersistEvent $event): void
  {
    $this->checkFlavor($event->getProjectEntity());
  }

  public function checkFlavor(Project $project): void
  {
    $request = $this->request_stack->getCurrentRequest();
    if (null == $request) {
      $project->setFlavor('pocketcode');
    } else {
      $project->setFlavor($request->attributes->get('flavor'));
    }
  }

  public static function getSubscribedEvents(): array
  {
    return [ProjectBeforePersistEvent::class => 'onEvent'];
  }
}
