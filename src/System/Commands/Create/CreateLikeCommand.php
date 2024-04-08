<?php

declare(strict_types=1);

namespace App\System\Commands\Create;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\User\Notifications\LikeNotification;
use App\DB\Entity\User\User;
use App\Project\ProjectManager;
use App\User\Notification\NotificationManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:like', description: 'like a project')]
class CreateLikeCommand extends Command
{
  public function __construct(private readonly UserManager $user_manager,
    private readonly ProjectManager $remix_manipulation_program_manager,
    private readonly EntityManagerInterface $entity_manager,
    private readonly NotificationManager $notification_service)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets liked')
      ->addArgument('user_name', InputArgument::REQUIRED, 'User who likes program')
    ;
  }

  /**
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $program_name = $input->getArgument('program_name');
    $user_name = $input->getArgument('user_name');

    $program = $this->remix_manipulation_program_manager->findOneByName($program_name);

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($user_name);

    if (null === $program || null === $user) {
      $output->writeln('Liking '.$program_name.' with user '.$user_name.' failed');

      return 1;
    }
    try {
      if ($program->getUser() !== $user) {
        $this->likeProgram($program, $user);
        $notification = new LikeNotification($program->getUser(), $user, $program);
        $notification->setSeen(boolval(random_int(0, 3)));
        $this->notification_service->addNotification($notification);
      }
    } catch (\Exception) {
      $output->writeln('Liking '.$program->getName().' with user '.$user_name.'failed');

      return 2;
    }
    $output->writeln('Liking '.$program->getName().' with user '.$user_name);

    return 0;
  }

  private function likeProgram(Program $program, User $user): void
  {
    $program_like = $this->entity_manager->getRepository(ProgramLike::class)->findOneBy(['program' => $program, 'user' => $user]);
    if (null === $program_like) {
      $like = new ProgramLike($program, $user, array_rand(ProgramLike::$TYPE_NAMES));
      $this->entity_manager->persist($like);
      $this->entity_manager->flush();
    }
  }
}
