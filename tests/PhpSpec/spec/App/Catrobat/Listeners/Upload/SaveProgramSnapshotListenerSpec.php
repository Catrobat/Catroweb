<?php

namespace tests\PhpSpec\spec\App\Catrobat\Listeners\Upload;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;
use App\Catrobat\Services\ProgramFileRepository;
use App\Entity\User;
use App\Entity\Program;
use Prophecy\Argument;
use App\Catrobat\Services\Time;

class SaveProgramSnapshotListenerSpec extends ObjectBehavior
{
  const STORAGE_DIR = "/path/to/storage/";

  private $user;
  private $program;
  private $file;


  public function let(ProgramFileRepository $repo, Time $time, User $user, Program $program, MyFile $file)
  {
    $this->beConstructedWith($time, $repo, self::STORAGE_DIR);
    $time->getTime()->willReturn(strtotime("2015-10-26 13:33:37"));

    $this->user = new User();
    $this->user->setLimited(true);

    $this->program = new Program();
    $this->program->setUser($this->user);
    $this->program->setId(1);

    $this->file = $file;
  }

  public function it_backups_the_current_program_file_of_a_limited_account_on_update(ProgramFileRepository $repo, Time $time)
  {
    $repo->getProgramFile(1)->willReturn($this->file);

    $this->saveProgramSnapshot($this->program);

    $this->file->move(self::STORAGE_DIR, "1_2015-10-26_13-33-37.catrobat")->shouldHaveBeenCalled();
  }

  public function it_does_not_backup_if_user_is_not_limited(ProgramFileRepository $repo, Time $time)
  {
    $this->user->setLimited(false);

    $this->saveProgramSnapshot($this->program);

    $this->file->move(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
  }

  public function it_does_not_backup_if_there_is_no_existing_file(ProgramFileRepository $repo, Time $time)
  {
    $repo->getProgramFile(1)->willThrow('\Symfony\Component\Filesystem\Exception\FileNotFoundException');

    $this->saveProgramSnapshot($this->program);

    $this->file->move(Argument::any(), Argument::any())->shouldNotHaveBeenCalled();
  }

  public function it_does_not_throw_an_exception_if_backup_fails(ProgramFileRepository $repo, Time $time)
  {
    $repo->getProgramFile(1)->willReturn($this->file);

    $this->file->move(Argument::any(), Argument::any())->willThrow('Symfony\Component\HttpFoundation\File\Exception\FileException');

    $this->saveProgramSnapshot($this->program);
  }

}

// Hack because HttpFoundation file constructor is broken for prophecies
class MyFile extends \SplFileInfo {
  public function move() {

  }
}