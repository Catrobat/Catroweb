<?php

namespace App\System\Commands\Create;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Studio\Studio;
use App\DB\Entity\User\User;
use App\Studio\StudioManager;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateStudioCommand extends Command
{
  public function __construct(
    private readonly EntityManagerInterface $entityManager,
    private readonly StudioManager $studioManager,
    private readonly UserManager $user_manager,
  ) {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this->setName('catrobat:studio')
      ->addArgument('name', InputArgument::REQUIRED, 'Name for the Studio')
      ->addArgument('description', InputArgument::REQUIRED, 'Description for the studio')
      ->addArgument('admin', InputArgument::REQUIRED, 'User which is admin on a studio')
      ->addArgument('is_public', InputArgument::REQUIRED, 'Sets studio to public or private')
      ->addArgument('is_enabled', InputArgument::REQUIRED, 'Enables studio')
      ->addArgument('allow_comments', InputArgument::REQUIRED, 'Enables comments in studios')
      ->addArgument('users', InputArgument::REQUIRED, 'Array of users for the studio')
      ->addArgument('status', InputArgument::REQUIRED, 'Array of statuses for the studio')
      ->addArgument('projects', InputArgument::REQUIRED, 'Array of projects for the studio')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $admin = $input->getArgument('admin');
    $name = $input->getArgument('name');
    $description = $input->getArgument('description');
    $isPublic = filter_var($input->getArgument('is_public'), FILTER_VALIDATE_BOOLEAN);
    $isEnabled = filter_var($input->getArgument('is_enabled'), FILTER_VALIDATE_BOOLEAN);
    $allowComments = filter_var($input->getArgument('allow_comments'), FILTER_VALIDATE_BOOLEAN);
    $usernames = $input->getArgument('users');
    $status = $input->getArgument('status');
    $projects = $input->getArgument('projects');
    $coverPath = null;

    /** @var User|null $adminUser */
    $adminUser = $this->user_manager->findUserByUsername($admin);
    if (null === $adminUser) {
      $output->writeln('Admin user not found.');

      return 1;
    }

    try {
      $ret = $this->createStudio($adminUser, $name, $description, $isPublic, $isEnabled, $usernames, $status, $allowComments, $projects, $coverPath);
      if (1 == $ret) {
        $output->writeln('User not found.');
      }
      $output->writeln('Studio created successfully.');

      return 0;
    } catch (\Exception $e) {
      $output->writeln('Failed to create studio: '.$e->getMessage());

      return 2;
    }
  }

  private function createStudio(User $admin, string $name, string $description, bool $is_public, bool $is_enabled, array $usernames, array $statuses, bool $allow_comments, array $projects, ?string $cover_path = null): int
  {
    $studio = (new Studio())
      ->setName($name)
      ->setDescription($description)
      ->setIsPublic($is_public)
      ->setIsEnabled($is_enabled)
      ->setAllowComments($allow_comments)
      ->setCoverPath($cover_path)
      ->setCreatedOn(new \DateTime())
    ;
    $this->entityManager->persist($studio);
    $this->entityManager->flush();
    $this->entityManager->refresh($studio);
    $this->studioManager->addAdminToStudio($admin, $studio);

    if ($is_public) {
      foreach (array_combine($usernames, $statuses) as $username => $status) {
        /** @var User|null $user */
        $user = $this->user_manager->findUserByUsername($username);
        if (null === $user) {
          return 1;
        }
        $this->studioManager->addUserToStudio($admin, $studio, $user);
      }

      foreach ($projects as $project) {
        /** @var Program $currentProject */
        $currentProject = $this->studioManager->findOneByName($project);
        /** @var User $user */
        $user = $this->user_manager->findUserByUsername($currentProject->getUser()->getUsername());
        $this->studioManager->addProjectToStudio($user, $studio, $currentProject);
      }
    } else {
      foreach (array_combine($usernames, $statuses) as $username => $status) {
        /** @var User|null $user */
        $user = $this->user_manager->findUserByUsername($username);
        if (null === $user) {
          return 1;
        }
        $this->studioManager->setJoinRequest($user, $studio, (string) $status);
      }
    }

    return 0;
  }
}
