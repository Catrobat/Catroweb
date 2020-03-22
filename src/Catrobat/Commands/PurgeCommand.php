<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class PurgeCommand.
 */
class PurgeCommand extends Command
{
  private ParameterBagInterface $parameter_bag;

  /**
   * PurgeCommand constructor.
   */
  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
  }

  protected function configure()
  {
    $this->setName('catrobat:purge')
      ->setDescription('Purge all database and file data')
      ->addOption('force')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): void
  {
    if (!$input->getOption('force'))
    {
      $output->writeln("This command will delete everything, use with caution! Use '--force' option if you are sure.");

      return;
    }

    $output->writeln('Deleting all catrobat data');

    $progress = new ProgressBar($output, 10);
    $progress->start();

    $progress->setMessage('Deleting Screenshots');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.screenshot.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Thumbnails');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.thumbnail.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Catrobat Files');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.file.storage.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Extracted Catrobat Files');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.file.extract.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Featured Images');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.featuredimage.dir'));
    $progress->advance();

    $progress->setMessage('Deleting APKs');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.apk.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Media Packages');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.mediapackage.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Templates');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.template.dir'));
    $progress->advance();

    $progress->setMessage('Dropping Database');
    CommandHelper::executeShellCommand(
      ['bin/console', 'doctrine:schema:drop', '--force'], [], 'Dropping database', $output
    );
    $progress->advance();

    $progress->setMessage('Dropping Migrations');
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:drop:migration'], [], 'Dropping the migration_versions table', $output);
    $progress->advance();

    $progress->setMessage('(Re-) Creating Database; executing migrations');
    CommandHelper::executeShellCommand(
      ['bin/console', 'doctrine:migrations:migrate'], [], 'Execute the migration to the latest version', $output
    );
    $progress->advance();

    $progress->finish();

    $output->writeln('');
  }
}
