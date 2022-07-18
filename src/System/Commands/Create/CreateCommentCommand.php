<?php

namespace App\System\Commands\Create;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\User;
use App\Project\ProgramManager;
use App\User\Notification\NotificationManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommentCommand extends Command
{
  public function __construct(private readonly UserManager $user_manager, private readonly EntityManagerInterface $em,
    private readonly ProgramManager $program_manager,
    private readonly NotificationManager $notification_service)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this->setName('catrobat:comment')
      ->setDescription('Add comment to a project')
      ->addArgument('user', InputArgument::REQUIRED, 'User who comments on program')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Program name of program to comment on')
      ->addArgument('message', InputArgument::REQUIRED, 'Comment message')
      ->addArgument('reported', InputArgument::REQUIRED, 'Boolean if it should be a reported comment')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $username = $input->getArgument('user');
    $program_name = $input->getArgument('program_name');
    $message = $input->getArgument('message');
    $reported = false;
    if (intval($input->getArgument('reported')) >= 1) {
      $reported = true;
    }

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($username);

    $program = $this->program_manager->findOneByName($program_name);

    if (null === $user || null === $program) {
      return 1;
    }

    try {
      $this->postComment($user, $program, $message, $reported);
    } catch (Exception) {
      return 2;
    }
    $output->writeln('Commenting '.$program->getName().' with user '.$user->getUsername());

    return 0;
  }

  private function postComment(User $user, Program $program, string $message, bool $reported): void
  {
    $temp_comment = new UserComment();
    $temp_comment->setUsername($user->getUsername());
    $temp_comment->setUser($user);
    $temp_comment->setText($message);
    $temp_comment->setProgram($program);
    $temp_comment->setUploadDate(date_create());
    $temp_comment->setIsReported($reported);

    $this->em->persist($temp_comment);
    $notification = new CommentNotification($program->getUser(), $temp_comment);
    $notification->setComment($temp_comment);
    $this->notification_service->addNotification($notification);

    $temp_comment->setNotification($notification);

    $this->em->persist($temp_comment);
    try {
      $notification->setSeen(boolval(random_int(0, 2)));
    } catch (Exception) {
      $notification->setSeen(false);
    }
    $this->em->persist($notification);
    $this->em->flush();
    $this->em->refresh($temp_comment);
  }
}
