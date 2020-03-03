<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\RemixManipulationProgramManager;
use App\Catrobat\Commands\Helpers\ResetController;
use App\Entity\Program;
use App\Entity\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateFeatureProgramCommand extends Command
{
  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * @var RemixManipulationProgramManager
   */
  private $remix_manipulation_program_manager;

  /**
   * @var ResetController
   */
  private $reset_controller;

  /**
   * FeaturedProgramCommand constructor.
   */
  public function __construct(UserManager $user_manager,
                              RemixManipulationProgramManager $program_manager,
                              ResetController $reset_controller)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->remix_manipulation_program_manager = $program_manager;
    $this->reset_controller = $reset_controller;
  }

  protected function configure()
  {
    $this->setName('catrobat:feature')
      ->setDescription('feature a project')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets featured')
    ;
  }

  /**
   * @throws \Exception
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    /**
     * @var Program
     */
    $program_name = $input->getArgument('program_name');

    $program = $this->remix_manipulation_program_manager->findOneByName($program_name);

    if (null == $program || null == $this->reset_controller)
    {
      return;
    }

    try
    {
      $this->reset_controller->featureProgram($program);
    }
    catch (\Exception $e)
    {
      return;
    }
    $output->writeln('Featuring '.$program->getName());
  }
}
