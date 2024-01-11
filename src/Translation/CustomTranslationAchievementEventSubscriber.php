<?php

namespace App\Translation;

use App\Project\ProjectManager;
use App\User\Achievements\AchievementManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class CustomTranslationAchievementEventSubscriber implements EventSubscriberInterface
{
  public function __construct(private readonly AchievementManager $achievement_manager, private readonly ProjectManager $program_manager, private readonly LoggerInterface $logger)
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

      $project = $this->program_manager->find($project_id);

      if (null === $project) {
        return;
      }

      $user = $project->getUser();

      if (null === $user) {
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
    return 'PUT' === $request->getMethod() && str_contains($request->getPathInfo(), '/translate/custom/project/');
  }

  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::TERMINATE => 'onTerminateEvent'];
  }
}
