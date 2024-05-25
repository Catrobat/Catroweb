<?php

declare(strict_types=1);

namespace App\System\Commands\Reset;

use App\Storage\FileHelper;
use App\System\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'catrobat:purge', description: 'Purge all database and file data')]
class PurgeCommand extends Command
{
  public function __construct(private readonly ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('force', 'f', InputOption::VALUE_NONE)
    ;
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    if (!$input->getOption('force')) {
      $output->writeln("This command will delete everything, use with caution! Use '--force' option if you are sure.");

      return 1;
    }

    $output->writeln('Deleting all catrobat data');

    $progress = new ProgressBar($output, 10);
    $progress->start();

    $progress->setMessage('Deleting Screenshots');
    FileHelper::emptyDirectory((string) $this->parameter_bag->get('catrobat.screenshot.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Thumbnails');
    FileHelper::emptyDirectory((string) $this->parameter_bag->get('catrobat.thumbnail.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Catrobat Files');
    FileHelper::emptyDirectory((string) $this->parameter_bag->get('catrobat.file.storage.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Extracted Catrobat Files');
    FileHelper::emptyDirectory((string) $this->parameter_bag->get('catrobat.file.extract.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Featured Images');
    FileHelper::emptyDirectory((string) $this->parameter_bag->get('catrobat.featuredimage.dir'));
    $progress->advance();

    $progress->setMessage('Deleting APKs');
    FileHelper::emptyDirectory((string) $this->parameter_bag->get('catrobat.apk.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Media Packages');
    FileHelper::emptyDirectory((string) $this->parameter_bag->get('catrobat.mediapackage.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Templates');
    FileHelper::emptyDirectory((string) $this->parameter_bag->get('catrobat.template.dir'));
    $progress->advance();

    $progress->setMessage('Dropping Database');
    CommandHelper::executeShellCommand(
      ['bin/console', 'doctrine:schema:drop', '--force'], [], 'Dropping database', $output
    );
    $progress->advance();

    $progress->setMessage('Dropping migrations');
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:drop:migration'], [], 'Dropping the doctrine_migration_versions table', $output);
    $progress->advance();

    $progress->setMessage('(Re-) Creating Database; executing migrations');
    CommandHelper::executeShellCommand(
      ['bin/console', 'doctrine:migrations:migrate', '--no-interaction'], ['timeout' => 320],
      'Execute the migration to the latest version', $output
    );

    $progress->advance();
    $progress->finish();

    $output->writeln('');

    return 0;
  }
}
