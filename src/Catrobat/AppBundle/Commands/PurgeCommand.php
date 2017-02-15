<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Helper\ProgressBar;
use Catrobat\AppBundle\Commands\Helpers\CommandHelper;

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

        $progress->setMessage('Droping Database');
        CommandHelper::executeSymfonyCommand('doctrine:schema:drop', $this->getApplication(), array('--force' => true), $suboutput);
        $progress->advance();

        $progress->setMessage('(Re-) Creating Database');
        CommandHelper::executeSymfonyCommand('doctrine:schema:create', $this->getApplication(),  array(), $suboutput);
        $progress->advance();

        $progress->finish();

        $output->writeln('');
    }

}
