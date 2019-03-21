<?php

namespace App\Catrobat\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Helper\ProgressBar;
use App\Catrobat\Commands\Helpers\CommandHelper;


/**
 * Class PurgeCommand
 * @package App\Catrobat\Commands
 */
class PurgeCommand extends ContainerAwareCommand
{
  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:purge')
      ->setDescription('Purge all database and file data')
      ->addOption('force');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    if (!$input->getOption('force'))
    {
      $output->writeln("This command will delete everything, use with caution! Use '--force' option if you are sure.");

      return;
    }

    $output->writeln('Deleting all catrobat data');

    $progress = new ProgressBar($output, 8);
    $progress->start();

    $suboutput = new NullOutput();

    $progress->setMessage('Deleting Screenshots');
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.screenshot.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Thumbnails');
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.thumbnail.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Catrobat Files');
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.file.storage.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Extracted Catrobat Files');
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.file.extract.dir'));
    $progress->advance();

    $progress->setMessage('Deleting Featured Images');
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.featuredimage.dir'));
    $progress->advance();

    $progress->setMessage('Deleting APKs');
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.apk.dir'));
    $progress->advance();

    $progress->setMessage('Dropping Database');
    CommandHelper::executeShellCommand('php bin/console doctrine:schema:drop --force', [],
      'Dropping database', $output);
    $progress->advance();

    $progress->setMessage('Dropping Migrations');
    CommandHelper::executeShellCommand('php bin/console catrobat:drop:migration', [],
      'Dropping the migration_versions table', $output);
    $progress->advance();

    $progress->setMessage('(Re-) Creating Database; executing migrations');
    CommandHelper::executeShellCommand('php bin/console doctrine:migrations:migrate', [],
      'Execute the migration to the latest version', $output);
    $progress->advance();

    $progress->finish();

    $output->writeln('');
  }

}
