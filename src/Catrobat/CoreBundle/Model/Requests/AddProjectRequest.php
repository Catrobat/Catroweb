<?php

namespace Catrobat\CoreBundle\Model\Requests;

use Symfony\Component\HttpFoundation\File\File;
use Catrobat\CoreBundle\Entity\User;

class AddProjectRequest
{
  private $user;
  private $projectfile;

  public function __construct(User $user, File $projectfile)
  {
    $this->user = $user;
    $this->projectfile = $projectfile;
  }

  public function getUser()
  {
    return $this->user;
  }

  public function setUser(User $user)
  {
    $this->user = $user;
  }

  public function getProjectfile()
  {
    return $this->projectfile;
  }

  public function setProjectfile(File $projectfile)
  {
    $this->projectfile = $projectfile;
  }

}