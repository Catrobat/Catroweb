<?php

namespace App\Catrobat\Listeners\Upload;

use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Program;
use App\Utils\TimeUtils;
use Exception;

class SaveProgramSnapshotListener
{
  private ProgramFileRepository $file_repository;

  private string $snapshot_dir;

  public function __construct(ProgramFileRepository $file_repository, string $snapshot_dir)
  {
    $this->file_repository = $file_repository;
    $this->snapshot_dir = $snapshot_dir;
  }

  public function handleEvent(ProgramAfterInsertEvent $event): void
  {
    $this->saveProgramSnapshot($event->getProgramEntity());
  }

  public function saveProgramSnapshot(Program $program): void
  {
    if ($program->getUser()->isLimited())
    {
      $file = null;
      try
      {
        $file = $this->file_repository->getProgramFile($program->getId());
        $date = date('Y-m-d_H-i-s', TimeUtils::getTimestamp());
        $file->move($this->snapshot_dir, $program->getId().'_'.$date.'.catrobat');
      }
      catch (Exception $exception)
      {
        return;
      }
    }
  }
}
