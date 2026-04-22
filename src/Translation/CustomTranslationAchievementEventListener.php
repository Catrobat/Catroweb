<?php

declare(strict_types=1);

namespace App\Translation;

use App\Project\ProjectManager;
use App\User\Achievements\AchievementManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

#[AsEventListener(event: KernelEvents::TERMINATE, method: 'onTerminateEvent')]
readonly class CustomTranslationAchievementEventListener
{
  public function __construct(
    private AchievementManager $achievement_manager,
    private ProjectManager $project_manager,
    private LoggerInterface $logger)
  {
  }

  public function onTerminateEvent(TerminateEvent $event): void
  {
    $status_code = $event->getResponse()->getStatusCode();
    if (200 !== $status_code) {
      return;
    }

    $request = $event->getRequest();

    if ($this->isCreateCustomTranslationAction($request)) {
      $project_id = $request->attributes->get('id');
      if (null === $project_id) {
        return;
      }

      $project = $this->project_manager->find($project_id);

      if (!$project instanceof \App\DB\Entity\Project\Project) {
        return;
      }

      $user = $project->getUser();

      if (!$user instanceof \App\DB\Entity\User\User) {
        return;
      }

      try {
        $this->achievement_manager->unlockAchievementCustomTranslation($user);
      } catch (\Exception $e) {
        $this->logger->warning('CustomTranslationAchievementListener: Exception unlocking achievement, user: '
          .$user->getId().', exception: '.$e->getMessage());
      }
    }
  }

  private function isCreateCustomTranslationAction(Request $request): bool
  {
    if ('PUT' !== $request->getMethod()) {
      return false;
    }

    $path = $request->getPathInfo();

    return 1 === preg_match('/\/projects\/[a-zA-Z0-9\-]+\/translation\/[a-z]+\/[a-zA-Z\-]+$/', $path);
  }
}
