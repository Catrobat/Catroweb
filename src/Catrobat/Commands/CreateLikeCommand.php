<?php


namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\RemixManipulationProgramManager;
use App\Catrobat\Commands\Helpers\ResetController;
use App\Entity\Program;
use App\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\UserManager;

class CreateLikeCommand extends Command
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
   * CreateLikeCommand constructor.
   *
   * @param UserManager                     $user_manager
   * @param RemixManipulationProgramManager $program_manager
   * @param ResetController                 $reset_controller
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

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:like')
      ->setDescription('like a project')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets liked')
      ->addArgument('user_name', InputArgument::REQUIRED, 'User who likes program');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $program_name = $input->getArgument('program_name');
    $user_name = $input->getArgument('user_name');

    /**
     * @var $program Program
     * @var $user    User
     */
    $program = $this->remix_manipulation_program_manager->findOneByName($program_name);
    $user = $this->user_manager->findUserByUsername($user_name);

    if ($program == null || $user == null || $this->reset_controller == null)
    {
      return;
    }

    try
    {
      $this->reset_controller->likeProgram($program, $user);
    } catch (\Exception $e)
    {
      return;
    }
    $output->writeln('Liking ' . $program->getName() . ' with user ' . $user_name);
  }
}
