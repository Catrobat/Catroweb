<?php

declare(strict_types=1);

namespace App\System\Commands\Create;

use App\DB\Entity\User\Notifications\FollowNotification;
use App\DB\Entity\User\User;
use App\User\Notification\NotificationManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:follow', description: 'follow an user')]
class CreateFollowersCommand extends Command
{
  public function __construct(private readonly UserManager $user_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly NotificationManager $notification_service)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this
      ->addArgument('user_name', InputArgument::REQUIRED, 'Name of user who gets followed')
      ->addArgument('follower', InputArgument::REQUIRED, 'User who follows')
    ;
  }

  /**
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $user_name = $input->getArgument('user_name');
    $follower_name = $input->getArgument('follower');

    if ($user_name == $follower_name) {
      return 1;
    }

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($user_name);

    /** @var User|null $follower */
    $follower = $this->user_manager->findUserByUsername($follower_name);

    if (null === $user || null === $follower) {
      return 2;
    }

    try {
      if ($user !== $follower) {
        $notification = new FollowNotification($user, $follower);
        $this->followUser($user, $follower);
        $this->notification_service->addNotification($notification);
      }
    } catch (\Exception) {
      return 3;
    }
    $output->writeln($follower_name.' follows '.$user_name);

    return 0;
  }

  private function followUser(User $user, User $follower): void
  {
    $user->addFollower($follower);
    $follower->addFollowing($user);

    $this->entity_manager->persist($user);
    $this->entity_manager->persist($follower);
    $this->entity_manager->flush();
  }
}
