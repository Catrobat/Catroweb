<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\Question;

use Catrobat\AppBundle\Commands\Helpers\CommandHelper;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;


/**
 * Class RefreshCommand
 * @package Catrobat\AppBundle\Commands
 */
class RefreshCommand extends ContainerAwareCommand
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
  public function __construct(Filesystem $filesystem)
  {
    parent::__construct();
    $this->filesystem = $filesystem;
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

    $kernel = $this->getContainer()->get('kernel');
    $env = $kernel->getEnvironment();
    $this->kernel = $kernel;

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
    $question = new Question('<question>Clear Cache (Y/n)? </question>', true);

    if ($dialog->doAsk($this->output, $question))
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
    $database_path = $this->kernel->getRootDir() . '/../sqlite/behattest.sqlite';
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
