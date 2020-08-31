<?php

namespace App\Catrobat\Listeners\Upload;

use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Program;
use App\Utils\TimeUtils;
use Exception;
use Psr\Log\LoggerInterface;

class SaveProgramSnapshotListener
{
  private ProgramFileRepository $file_repository;

  private string $snapshot_dir;

  private LoggerInterface $logger;

  public function __construct(ProgramFileRepository $file_repository, string $snapshot_dir, LoggerInterface $logger)
  {
    $this->file_repository = $file_repository;
    $this->snapshot_dir = $snapshot_dir;
    $this->logger = $logger;
  }

  public function handleEvent(ProgramAfterInsertEvent $event): void
  {
    $project = $event->getProgramEntity();

    if ($project->isSnapshotsEnabled())
    {
      $this->saveProgramSnapshot($project);
    }
  }

  public function saveProgramSnapshot(Program $program): void
  {
    try
    {
      $file = $this->file_repository->getProgramFile($program->getId());
      $date = TimeUtils::getDateTime()->format('Y-m-d_H-i-s');
      $file->move($this->snapshot_dir, $program->getId().'__'.$date.'.catrobat');
    }
    catch (Exception $e)
    {
      $this->logger->error($e->getCode().': Failed to create Snapshot on project update: '.$e->getMessage());
    }
  }
}
