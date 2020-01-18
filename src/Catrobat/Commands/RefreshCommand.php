<?php

namespace App\Catrobat\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use App\Catrobat\Commands\Helpers\CommandHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;


/**
 * Class RefreshCommand
 * @package App\Catrobat\Commands
 */
class RefreshCommand extends Command
{
  /**
   * @var Input
   */
  protected $input;
  /**
   * @var Output
   */
  protected $output;
  /**
   * @var Filesystem
   */
  protected $filesystem;
  /**
   * @var Kernel
   */
  protected $kernel;

  /**
   * RefreshCommand constructor.
   *
   * @param Filesystem $filesystem
   */
  public function __construct(Filesystem $filesystem, KernelInterface $kernel)
  {
    parent::__construct();
    $this->filesystem = $filesystem;
    $this->kernel = $kernel;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:refresh')
      ->setDescription('Refresh all caches and fixtures')
    ;
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
    $this->input = $input;
    $this->output = $output;

    $env = $this->kernel->getEnvironment();

    switch ($env)
    {
      case 'test':
        $this->generateTestdata();
        $this->deleteSqLiteDatabase();
        break;
    }
    $this->clearCache();

    $output->writeln('<info>');
    $output->writeln('Make sure to run this command in all environments!');
    $output->writeln($this->getName() . ' --env=test');
    $output->writeln($this->getName() . ' --env=prod');
    $output->writeln('</info>');
  }

  /**
   * @throws \Exception
   */
  protected function clearCache()
  {
    $dialog = $this->getHelperSet()->get('question');
    $question = new ConfirmationQuestion('<question>Clear Cache (Y/n)? </question>', true);

    if ($dialog->ask($this->input, $this->output, $question))
    {
      CommandHelper::executeSymfonyCommand('cache:clear', $this->getApplication(), [], $this->output);
    }
  }

  /**
   * @throws \Exception
   */
  protected function generateTestdata()
  {
    CommandHelper::executeSymfonyCommand('catrobat:test:generate', $this->getApplication(), [], $this->output);
  }

  /**
   *
   */
  protected function deleteSqLiteDatabase()
  {
    $database_path = $this->kernel->getRootDir() . '/../tests/behat/sqlite/behattest.sqlite';
    $this->output->write('Deleting SQLite database (' . $database_path . ')... ');
    try
    {
      $this->filesystem->remove($database_path);
      $this->output->writeln(' done!');
    } catch (IOException $e)
    {
      $this->output->writeln('Could not delete ' . $e->getPath());
    }
  }
}
