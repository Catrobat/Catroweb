<?php

namespace tests;

use App\Entity\NolbExampleProgram;
use App\Entity\Program;
use App\Entity\User;


class NolbExampleProgramTest extends \PHPUnit\Framework\TestCase
{
  private $user;
  private $program;
  /* @var $nolb_example_program NolbExampleProgram */
  private $nolb_example_program;

  protected function setUp()
  {
    parent::setUp();
    $this->user = new User();
    $this->program = new Program();

    $this->program->setUser($this->user);
    $this->nolb_example_program = new NolbExampleProgram();
    $this->nolb_example_program->setProgram($this->program);

    $this->nolb_example_program->setDownloadsFromFemale(3);
    $this->nolb_example_program->setDownloadsFromMale(1337);
  }

  public function testSetGender()
  {
    $this->nolb_example_program->setIsForFemale(true);
    self::assertTrue($this->nolb_example_program->getIsForFemale());

    $this->nolb_example_program->setIsForMale(true);
    self::assertTrue($this->nolb_example_program->getIsForMale());
  }

  public function testGenderedDownloadIncrease()
  {
    self::assertEquals(3, $this->nolb_example_program->getDownloadsFromFemale(), "Female downloads didnt match");
    self::assertEquals(1337, $this->nolb_example_program->getDownloadsFromMale(), "Male downloads didnt match");

    $this->nolb_example_program->increaseFemaleDownloads();
    $this->nolb_example_program->increaseFemaleDownloads();
    $this->nolb_example_program->increaseFemaleDownloads();
    $this->nolb_example_program->increaseFemaleDownloads();
    self::assertEquals(7, $this->nolb_example_program->getDownloadsFromFemale(), "Female downloads didnt match");

    $this->nolb_example_program->increaseMaleDownloads();
    $this->nolb_example_program->increaseMaleDownloads();
    $this->nolb_example_program->increaseMaleDownloads();
    self::assertEquals(1340, $this->nolb_example_program->getDownloadsFromMale(), "Male downloads didnt match");

  }


}
