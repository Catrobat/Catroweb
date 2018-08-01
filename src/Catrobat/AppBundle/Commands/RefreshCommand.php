<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Filesystem\Filesystem;
use Catrobat\AppBundle\Commands\Helpers\CommandHelper;

class RefreshCommand extends ContainerAwareCommand
{
    protected $input;
    protected $output;
    protected $filesystem;
    protected $kernel;

    public function __construct(Filesystem $filesystem)
    {
        parent::__construct();
        $this->filesystem = $filesystem;
    }

    protected function configure()
    {
        $this->setName('catrobat:refresh')
            ->setDescription('Refresh all caches and fixtures')
//     ->addArgument('directory', InputArgument::REQUIRED, 'Direcory contaning catrobat files for import')
//     ->addArgument('user', InputArgument::REQUIRED, 'User who will be the ower of these programs');
      ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $kernel = $this->getContainer()->get('kernel');
        $env = $kernel->getEnvironment();
        $this->kernel = $kernel;

        switch ($env) {
          case 'test':
            $this->generateTestdata();
            $this->deleteSqLiteDatabase();
          break;
        }
        $this->clearCache();

        $output->writeln('<info>');
        $output->writeln('Make sure to run this command in all environments!');
        $output->writeln($this->getName().' --env=test');
        $output->writeln($this->getName().' --env=prod');
        $output->writeln('</info>');
    }

    protected function clearCache()
    {
        $dialog = $this->getHelperSet()->get('question');
        $question = new Question('<question>Clear Cache (Y/n)? </question>', true);

        if ($dialog->doAsk($this->output, $question)) {
            CommandHelper::executeSymfonyCommand('cache:clear', $this->getApplication(), array(), $this->output);
        }
    }

    protected function generateTestdata()
    {
        CommandHelper::executeSymfonyCommand('catrobat:test:generate', $this->getApplication(), array(), $this->output);
    }
    
    protected function deleteSqLiteDatabase()
    {
        $database_path = $this->kernel->getRootDir().'/../sqlite/behattest.sqlite';
        $this->output->write('Deleting SQLite database ('.$database_path.')... ');
        try {
            $this->filesystem->remove($database_path);
            $this->output->writeln(' done!');
        } catch (IOExceptionInterface $e) {
            $this->output->writeln('Could not delete '.$e->getPath());
        }
    }
}
