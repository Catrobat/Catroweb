<?php

declare(strict_types=1);

namespace App\Project\CatrobatFile;

use App\DB\Entity\Flavor;
use App\DB\Entity\Project\Program;
use App\Project\Event\ProjectBeforePersistEvent;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsEventListener(event: ProjectBeforePersistEvent::class, method: 'onEvent')]
readonly class ProjectFlavorEventListener
{
  public function __construct(private RequestStack $request_stack)
  {
  }

  public function onEvent(ProjectBeforePersistEvent $event): void
  {
    $this->checkFlavor($event->getProjectEntity());
  }

  public function checkFlavor(Program $project): void
  {
    $request = $this->request_stack->getCurrentRequest();
    if (null == $request) {
      $project->setFlavor(Flavor::POCKETCODE);
    } else {
      $project->setFlavor($request->attributes->get('flavor'));
    }
  }
}
