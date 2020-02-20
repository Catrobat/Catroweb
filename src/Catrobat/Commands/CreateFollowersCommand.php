<?php


namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\RemixManipulationProgramManager;
use App\Catrobat\Commands\Helpers\ResetController;
use App\Entity\User;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\UserManager;

class CreateFollowersCommand extends Command
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
   * CreateFollowersCommand constructor.
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
    $this->setName('catrobat:follow')
      ->setDescription('follow an user')
      ->addArgument('user_name', InputArgument::REQUIRED, 'Name of user who gets followed')
      ->addArgument('follower', InputArgument::REQUIRED, 'User who follows');
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
    $user_name = $input->getArgument('user_name');
    $follower_name = $input->getArgument('follower');

    if ($user_name == $follower_name)
    {
      return;
    }

    /**
     * @var $user     User
     * @var $follower User
     */
    $user = $this->user_manager->findUserByUsername($user_name);
    $follower = $this->user_manager->findUserByUsername($follower_name);

    if ($user == null || $follower == null || $this->reset_controller == null)
    {
      return;
    }

    try
    {
      $this->reset_controller->followUser($user, $follower);
    } catch (\Exception $e)
    {
      return;
    }
    $output->writeln($follower_name . ' follows ' . $user_name);
  }
}
