<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\Input;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Class RefreshCommand.
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
   */
  public function __construct(Filesystem $filesystem, KernelInterface $kernel)
  {
    parent::__construct();
    $this->filesystem = $filesystem;
    $this->kernel = $kernel;
  }

  protected function configure()
  {
    $this->setName('catrobat:refresh')
      ->setDescription('Refresh all caches and fixtures')
    ;
  }

  /**
   * @throws \Exception
   *
   * @return int|void|null
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
        break;
    }
    $this->clearCache();

    $output->writeln('<info>');
    $output->writeln('Make sure to run this command in all environments!');
    $output->writeln($this->getName().' --env=test');
    $output->writeln($this->getName().' --env=prod');
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
}
