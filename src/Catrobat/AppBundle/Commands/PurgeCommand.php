<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Helper\ProgressBar;

class PurgeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('catrobat:purge')
    ->setDescription('Purge all database and file data')
    ->addOption('force');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (!$input->getOption('force')) {
            $output->writeln("This command will delete everything, use with caution! Use '--force' option if you are sure.");

            return;
        }

        $output->writeln('Deleting all catrobat data');

        $progress = new ProgressBar($output, 7);
        $progress->start();

        $suboutput = new NullOutput();

        $progress->setMessage('Deleting Screenshots');
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.screenshot.dir'));
        $progress->advance();

        $progress->setMessage('Deleting Thumbnails');
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.thumbnail.dir'));
        $progress->advance();

        $progress->setMessage('Deleting Catrobat Files');
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.file.storage.dir'));
        $progress->advance();

        $progress->setMessage('Deleting Extracted Catrobat Files');
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.file.extract.dir'));
        $progress->advance();

        $progress->setMessage('Deleting Featured Images');
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.featuredimage.dir'));
        $progress->advance();

        $progress->setMessage('Deleting APKs');
        $this->emptyDirectory($this->getContainer()->getParameter('catrobat.apk.dir'));
        $progress->advance();

        $progress->setMessage('Droping Database');
        $this->executeSymfonyCommand('doctrine:schema:drop', array('--force' => true), $suboutput);
        $progress->advance();

        $progress->setMessage('(Re-) Creating Database');
        $this->executeSymfonyCommand('doctrine:schema:create', array(), $suboutput);
        $progress->advance();

        $progress->finish();

        $output->writeln('');
    }

    private function emptyDirectory($directory)
    {
        $filesystem = new Filesystem();

        $finder = new Finder();
        $finder->in($directory)->depth(0);
        foreach ($finder as $file) {
            $filesystem->remove($file);
        }
    }

    private function executeSymfonyCommand($command, $args, $output)
    {
        $command = $this->getApplication()->find($command);
        $args['command'] = $command;
        $input = new ArrayInput($args);
        $command->run($input, $output);
    }
}
