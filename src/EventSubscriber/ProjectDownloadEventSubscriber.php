<?php

namespace App\EventSubscriber;

use App\Entity\ProgramManager;
use App\Event\ProjectDownloadEvent;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProjectDownloadEventSubscriber implements EventSubscriberInterface
{
  protected ProgramManager $program_manager;
  protected LoggerInterface $logger;

  public function __construct(ProgramManager $program_manager, LoggerInterface $downloadLogger)
  {
    $this->logger = $downloadLogger; // Automatically injects the download logger here thx to this syntax. (camelCase)
    $this->program_manager = $program_manager;
  }

  public function onProjectDownload(ProjectDownloadEvent $event): void
  {
    $sessionKey = 'projectDownloadList_'.$event->getDownloadType();
    $downloadList = $event->getRequest()->getSession()->get($sessionKey, []);
    if (!in_array($event->getProject()->getId(), $downloadList, true)) {
      $this->program_manager->increaseDownloads($event->getProject(), $event->getUser());
      $downloadList[] = $event->getProject()->getId();
      $event->getRequest()->getSession()->set($sessionKey, $downloadList);
    }
  }

  public static function getSubscribedEvents(): array
  {
    return [
      ProjectDownloadEvent::class => 'onProjectDownload',
    ];
  }
}
