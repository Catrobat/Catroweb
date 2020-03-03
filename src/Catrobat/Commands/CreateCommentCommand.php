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


/**
 * Class CreateCommentCommand
 * @package App\Catrobat\Commands
 */
class CreateCommentCommand extends Command
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
   * ProgramImportCommand constructor.
   *
   * @param UserManager                     $user_manager
   * @param RemixManipulationProgramManager $program_manager
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
    $this->setName('catrobat:comment')
      ->setDescription('Add comment to a project')
      ->addArgument('user', InputArgument::REQUIRED, 'User who comments on program')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Program name of program to comment on')
      ->addArgument('message', InputArgument::REQUIRED, 'Comment message')
      ->addArgument('reported', InputArgument::REQUIRED, 'Boolean if it should be a reported comment');
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
    /**
     * @var $user    User
     * @var $program Program
     */

    $username = $input->getArgument('user');
    $program_name = $input->getArgument('program_name');
    $message = $input->getArgument('message');
    $reported = $input->getArgument('reported');

    $user = $this->user_manager->findUserByUsername($username);
    $program = $this->remix_manipulation_program_manager->findOneByName($program_name);

    if ($user == null || $program == null)
    {
      return;
    }

    try
    {
      $this->reset_controller->postComment($user, $program, $message, $reported);
    } catch (\Exception $e)
    {
      return;
    }
    $output->writeln('Commenting on ' . $program->getName() . ' with user ' . $user->getUsername());
  }
}
