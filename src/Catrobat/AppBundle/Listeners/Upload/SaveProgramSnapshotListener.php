<?php

namespace Catrobat\AppBundle\Listeners\Upload;

use Catrobat\AppBundle\Services\ProgramFileRepository;
use Catrobat\AppBundle\Events\ProgramAfterInsertEvent;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Catrobat\AppBundle\Services\Time;

class SaveProgramSnapshotListener
{

  private $file_repository;
  private $snapshot_dir;
  private $time;

  public function __construct(Time $time, ProgramFileRepository $file_repository, $snapshot_dir)
  {
    $this->file_repository = $file_repository;
    $this->snapshot_dir = $snapshot_dir;
    $this->time = $time;
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
        $date = date("Y-m-d_H-i-s", $this->time->getTime());
        $file->move($this->snapshot_dir, $program->getId() . "_" . $date . ".catrobat");
      } catch (\Exception $exception)
      {
        return;
      }
    }
  }
}