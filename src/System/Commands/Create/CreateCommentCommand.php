<?php

namespace App\System\Commands\Create;

use App\DB\Entity\Project\Program;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\Entity\User\Notifications\CommentNotification;
use App\DB\Entity\User\User;
use App\Project\ProgramManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateCommentCommand extends Command
{
  protected static $defaultDescription = 'Add comment to a project';

  public function __construct(private readonly UserManager $user_manager, private readonly EntityManagerInterface $em,
    private readonly ProgramManager $program_manager,
  ) {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this->setName('catrobat:comment')
      ->addArgument('user', InputArgument::REQUIRED, 'User who comments on program')
      ->addArgument('program_name', InputArgument::REQUIRED, 'Program name of program to comment on')
      ->addArgument('message', InputArgument::REQUIRED, 'Comment message')
      ->addArgument('reported', InputArgument::REQUIRED, 'Boolean if it should be a reported comment')
    ;
  }

  /**
   * @throws \Exception
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
      return \Symfony\Component\Console\Command\Command::FAILURE;
    }

    try {
      $this->postComment($user, $program, $message, $reported);
    } catch (\Exception) {
      return \Symfony\Component\Console\Command\Command::INVALID;
    }
    $output->writeln('Commenting '.$program->getName().' with user '.$user->getUsername());

    return \Symfony\Component\Console\Command\Command::SUCCESS;
  }

  private function postComment(User $user, Program $program, string $message, bool $reported): void
  {
    $comment = new UserComment();
    $comment->setUsername($user->getUsername());
    $comment->setUser($user);
    $comment->setText($message);
    $comment->setProgram($program);
    $comment->setUploadDate(date_create());
    $comment->setIsReported($reported);

    $this->em->persist($comment);

    $notification = new CommentNotification($program->getUser(), $comment);
    $notification->setComment($comment);
    $notification->setSeen(random_int(0, 2) > 1);
    $this->em->persist($notification);

    $comment->setNotification($notification);
    $this->em->persist($comment);
    $this->em->flush();
  }
}
