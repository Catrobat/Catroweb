<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\User\User;
use App\Project\AddProjectRequest;
use App\Project\ProjectManager;
use App\User\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

/**
 * Restores lost project files from .catrobat archives: each archive is uploaded as an update of the user's existing
 * project with the same name (same id, stats and upload date kept, has_missing_files cleared, no follower notices). Archives without such a project are
 * skipped, never added as new projects. Unlike catrobat:import, the archive is stored unchanged.
 */
#[AsCommand(
  name: 'catrobat:projects:restore-files',
  description: "Re-upload .catrobat archives as updates of a user's existing projects with the same name",
)]
class RestoreProjectFilesCommand extends Command
{
  public function __construct(
    private readonly UserManager $user_manager,
    private readonly ProjectManager $project_manager,
  ) {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addArgument('directory', InputArgument::REQUIRED, 'Directory with the .catrobat archives')
      ->addArgument('user', InputArgument::REQUIRED, 'Username owning the projects')
      ->addOption('dry-run', null, InputOption::VALUE_NONE, 'List what would be restored')
      ->addOption('limit', null, InputOption::VALUE_REQUIRED, 'Restore at most N projects (0 = all)', '0')
    ;
  }

  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $io = new SymfonyStyle($input, $output);
    $dry_run = true === $input->getOption('dry-run');
    $limit = max(0, (int) $input->getOption('limit'));

    $user = $this->user_manager->findUserByUsername($input->getArgument('user'));
    if (!$user instanceof User) {
      $io->error('User not found.');

      return Command::FAILURE;
    }

    $restored = 0;
    $skipped = 0;
    $failed = 0;
    foreach ((new Finder())->files()->name('*.catrobat')->in($input->getArgument('directory'))->depth(0)->sortByName() as $file) {
      if ($limit > 0 && $restored >= $limit) {
        break;
      }

      $name = self::projectName($file->getPathname()) ?? '';
      $project = '' === $name ? null : $this->project_manager->findOneByNameAndUser($name, $user);
      if (null === $project) {
        ++$skipped;
        $io->writeln(sprintf('%s: skipped, no project named "%s"', $file->getFilename(), $name));
        continue;
      }

      if ($dry_run) {
        ++$restored;
        $io->writeln(sprintf('%s: would restore %s "%s"', $file->getFilename(), $project->getId() ?? '', $name));
        continue;
      }

      try {
        $this->project_manager->addProject(new AddProjectRequest($user, new File($file->getPathname()), null, null, $project->getFlavor(), $project->getId(), restore: true));
        ++$restored;
        $io->writeln(sprintf('%s: restored %s "%s"', $file->getFilename(), $project->getId() ?? '', $name));
      } catch (\Throwable $throwable) {
        ++$failed;
        $io->warning(sprintf('%s: %s', $file->getFilename(), $throwable->getMessage()));
      }
    }

    $summary = sprintf('%d restored, %d skipped, %d failed.', $restored, $skipped, $failed);
    if ($failed > 0) {
      $io->warning($summary);

      return Command::FAILURE;
    }

    $io->success($summary);

    return Command::SUCCESS;
  }

  private static function projectName(string $path): ?string
  {
    $zip = new \ZipArchive();
    if (true !== $zip->open($path)) {
      return null;
    }

    $xml = $zip->getFromName('code.xml');
    $zip->close();
    if (false === $xml) {
      return null;
    }

    $code = @simplexml_load_string($xml);

    return false === $code ? null : trim((string) $code->header?->programName);
  }
}
