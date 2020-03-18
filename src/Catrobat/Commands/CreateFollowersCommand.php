<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\RemixManipulationProgramManager;
use App\Catrobat\Commands\Helpers\ResetController;
use App\Catrobat\Services\CatroNotificationService;
use App\Entity\FollowNotification;
use App\Entity\User;
use App\Entity\UserManager;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

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
   * @var CatroNotificationService
   */
  private $notification_service;

  /**
   * CreateFollowersCommand constructor.
   */
  public function __construct(UserManager $user_manager,
                              RemixManipulationProgramManager $program_manager,
                              ResetController $reset_controller,
                              CatroNotificationService $notification_service)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->remix_manipulation_program_manager = $program_manager;
    $this->reset_controller = $reset_controller;
    $this->notification_service = $notification_service;
  }

  protected function configure()
  {
    $this->setName('catrobat:follow')
      ->setDescription('follow an user')
      ->addArgument('user_name', InputArgument::REQUIRED, 'Name of user who gets followed')
      ->addArgument('follower', InputArgument::REQUIRED, 'User who follows')
    ;
  }

  /**
   * @throws \Exception
   *
   * @return int|void|null
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
     * @var User
     * @var User $follower
     */
    $user = $this->user_manager->findUserByUsername($user_name);
    $follower = $this->user_manager->findUserByUsername($follower_name);

    if (null == $user || null == $follower || null == $this->reset_controller)
    {
      return;
    }

    try
    {
      $notification = new FollowNotification($user, $follower);
      $this->reset_controller->followUser($user, $follower);
      $this->notification_service->addNotification($notification);
    }
    catch (\Exception $e)
    {
      return;
    }
    $output->writeln($follower_name.' follows '.$user_name);
  }
}
