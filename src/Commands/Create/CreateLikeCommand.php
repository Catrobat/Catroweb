<?php

namespace App\Commands\Create;

use App\Catrobat\Services\CatroNotificationService;
use App\Entity\LikeNotification;
use App\Entity\Program;
use App\Entity\ProgramLike;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateLikeCommand extends Command
{
  protected static $defaultName = 'catrobat:like';

  private UserManager $user_manager;

  private ProgramManager $remix_manipulation_program_manager;

  private CatroNotificationService $notification_service;

  private EntityManagerInterface $entity_manager;

  public function __construct(UserManager $user_manager,
                              ProgramManager $program_manager,
                              EntityManagerInterface $entity_manager,
                              CatroNotificationService $notification_service)
  {
    parent::__construct();
    $this->user_manager = $user_manager;
    $this->remix_manipulation_program_manager = $program_manager;
    $this->entity_manager = $entity_manager;
    $this->notification_service = $notification_service;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:like')
      ->setDescription('like a project')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Name of program  which gets liked')
      ->addArgument('user_name', InputArgument::REQUIRED, 'User who likes program')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $program_name = $input->getArgument('program_name');
    $user_name = $input->getArgument('user_name');

    $program = $this->remix_manipulation_program_manager->findOneByName($program_name);

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($user_name);

    if (null === $program || null === $user)
    {
      $output->writeln('Liking '.$program_name.' with user '.$user_name.' failed');

      return 1;
    }
    try
    {
      if ($program->getUser() !== $user)
      {
        $notification = new LikeNotification($program->getUser(), $user, $program);
        $this->likeProgram($program, $user);
        $notification->setSeen(boolval(random_int(0, 3)));
        $this->notification_service->addNotification($notification);
      }
    }
    catch (Exception $e)
    {
      $output->writeln('Liking '.$program->getName().' with user '.$user_name.'failed');

      return 2;
    }
    $output->writeln('Liking '.$program->getName().' with user '.$user_name);

    return 0;
  }

  private function likeProgram(Program $program, User $user): void
  {
    $like = new ProgramLike($program, $user, array_rand(ProgramLike::$TYPE_NAMES));
    $like->setCreatedAt(date_create());

    $this->entity_manager->persist($like);
    $this->entity_manager->flush();
  }
}
