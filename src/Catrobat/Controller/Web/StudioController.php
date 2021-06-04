<?php

namespace App\Catrobat\Controller\Web;

use App\Entity\ProgramManager;
use App\Entity\UserManager;
use App\Manager\StudioManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class StudioController extends AbstractController
{
  protected StudioManager $studio_manager;
  protected UserManager $user_manager;
  protected ProgramManager $program_manager;

  public function __construct(StudioManager $studio_manager, UserManager $user_manager, ProgramManager $program_manager)
  {
    $this->studio_manager = $studio_manager;
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
  }
}
