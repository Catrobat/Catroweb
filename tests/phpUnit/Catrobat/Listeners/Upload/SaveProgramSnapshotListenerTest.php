<?php

namespace Tests\phpUnit\Catrobat\Listeners\Upload;

use App\Catrobat\Listeners\Upload\SaveProgramSnapshotListener;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\Program;
use App\Entity\User;
use App\Utils\TimeUtils;
use DateTime;
use Monolog\Logger;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 * @covers \App\Catrobat\Listeners\Upload\SaveProgramSnapshotListener
 */
class SaveProgramSnapshotListenerTest extends TestCase
{
  const STORAGE_DIR = '/path/to/storage/';

  private Program $program;

  private SaveProgramSnapshotListener $save_program_snapshot_listener;

  /**
   * @var MockObject|ProgramFileRepository
   */
  private $project_file_repository;

  /**
   * @var File|MockObject
   */
  private $file;

  protected function setUp(): void
  {
    $this->project_file_repository = $this->createMock(ProgramFileRepository::class);
    $logger = $this->createMock(Logger::class);

    $this->file = $this->createMock(File::class);

    $this->save_program_snapshot_listener = new SaveProgramSnapshotListener($this->project_file_repository, self::STORAGE_DIR, $logger);

    TimeUtils::freezeTime(new DateTime('2015-10-26 13:33:37'));

    $this->program = new Program();
    $this->program->setUser(new User());
    $this->program->setId('1');
  }

  public function testBackupsTheCurrentProgramFileOnUpdate(): void
  {
    $this->project_file_repository->expects($this->atLeastOnce())
      ->method('getProgramFile')->with(1)->willReturn($this->file);
    $this->file->expects($this->atLeastOnce())
      ->method('move')->with(self::STORAGE_DIR, '1__2015-10-26_13-33-37.catrobat');
    $this->save_program_snapshot_listener->saveProgramSnapshot($this->program);
  }

  public function testDoesNotBackupIfThereIsNoExistingFile(): void
  {
    $this->project_file_repository->expects($this->atLeastOnce())
      ->method('getProgramFile')->with(1)->willThrowException(new FileNotFoundException());
    $this->file->expects($this->never())->method('move');
    $this->save_program_snapshot_listener->saveProgramSnapshot($this->program);
  }

  public function testDoesNotThrowAnExceptionIfBackupFails(): void
  {
    $this->project_file_repository->expects($this->atLeastOnce())
      ->method('getProgramFile')->with(1)->willReturn($this->file);
    $this->file->expects($this->atLeastOnce())->method('move')->willThrowException(new FileException());
    $this->save_program_snapshot_listener->saveProgramSnapshot($this->program);
  }
}
