<?php

namespace Catrobat\AppBundle\Model\Requests;

use Symfony\Component\HttpFoundation\File\File;
use Catrobat\AppBundle\Entity\User;

class AddProgramRequest
{
  private $user;
  private $programfile;

  public function __construct(User $user, File $programfile)
  {
    $this->user = $user;
    $this->programfile = $programfile;
  }

  public function getUser()
  {
    return $this->user;
  }

  public function setUser(User $user)
  {
    $this->user = $user;
  }

  public function getProgramfile()
  {
    return $this->programfile;
  }

  public function setProgramfile(File $programfile)
  {
    $this->programfile = $programfile;
  }

}