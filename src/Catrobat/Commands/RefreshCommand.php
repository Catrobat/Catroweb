<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpKernel\KernelInterface;

class RefreshCommand extends Command
{
  protected static $defaultName = 'catrobat:refresh';
  protected InputInterface $input;

  protected OutputInterface $output;

  protected Filesystem $filesystem;

  protected KernelInterface $kernel;

  public function __construct(Filesystem $filesystem, KernelInterface $kernel)
  {
    parent::__construct();
    $this->filesystem = $filesystem;
    $this->kernel = $kernel;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:refresh')
      ->setDescription('Refresh all caches and fixtures')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
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

    return 0;
  }

  /**
   * @throws Exception
   */
  protected function clearCache(): void
  {
    $dialog = $this->getHelper('question');
    $question = new ConfirmationQuestion('<question>Clear Cache (Y/n)? </question>', true);

    if ($dialog->ask($this->input, $this->output, $question))
    {
      CommandHelper::executeSymfonyCommand('cache:clear', $this->getApplication(), [], $this->output);
    }
  }

  /**
   * @throws Exception
   */
  protected function generateTestdata(): void
  {
    CommandHelper::executeSymfonyCommand('catrobat:test:generate', $this->getApplication(), [], $this->output);
  }
}
