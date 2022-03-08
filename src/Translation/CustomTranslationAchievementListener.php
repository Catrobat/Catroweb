<?php

namespace App\Translation;

use App\Project\ProgramManager;
use App\User\Achievements\AchievementManager;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class CustomTranslationAchievementListener
{
  private AchievementManager $achievement_manager;
  private ProgramManager $program_manager;
  private LoggerInterface $logger;

  public function __construct(AchievementManager $achievement_manager, ProgramManager $program_manager, LoggerInterface $logger)
  {
    $this->achievement_manager = $achievement_manager;
    $this->program_manager = $program_manager;
    $this->logger = $logger;
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
      } catch (Exception $e) {
        $this->logger->warning('CustomTranslationAchievementListener: Exception unlocking achievement, user: '
          .$user->getId().', exception: '.$e->getMessage());
      }
    }
  }

  private function isCreateCustomTranslationAction(Request $request): bool
  {
    return 'PUT' === $request->getMethod() && str_contains($request->getPathInfo(), '/translate/custom/project/');
  }
}
