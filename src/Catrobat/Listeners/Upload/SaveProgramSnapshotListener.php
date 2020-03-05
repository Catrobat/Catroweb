<?php

namespace App\Catrobat\Listeners\Upload;

use App\Catrobat\Events\ProgramAfterInsertEvent;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Program;
use App\Utils\TimeUtils;

/**
 * Class SaveProgramSnapshotListener.
 */
class SaveProgramSnapshotListener
{
  /**
   * @var ProgramFileRepository
   */
  private $file_repository;
  /**
   * @var
   */
  private $snapshot_dir;

  /**
   * SaveProgramSnapshotListener constructor.
   *
   * @param $snapshot_dir
   */
  public function __construct(ProgramFileRepository $file_repository, $snapshot_dir)
  {
    $this->file_repository = $file_repository;
    $this->snapshot_dir = $snapshot_dir;
  }

  public function handleEvent(ProgramAfterInsertEvent $event)
  {
    $this->saveProgramSnapshot($event->getProgramEntity());
  }

  public function saveProgramSnapshot(Program $program)
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
      catch (\Exception $exception)
      {
        return;
      }
    }
  }
}
