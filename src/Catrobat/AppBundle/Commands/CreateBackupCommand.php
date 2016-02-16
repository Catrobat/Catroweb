<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;

class CreateBackupCommand extends ContainerAwareCommand
{
    public $output;

    protected function configure()
    {
        $this->setName('catrobat:backup:create')
             ->setDescription('Generates a backup')
             ->addArgument('backupName', InputArgument::OPTIONAL, 'Backupname without extension');;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $backup_dir = realpath($this->getContainer()->getParameter('catrobat.backup.dir'));
        $output->writeln('Using backup directory '.$backup_dir);

        if ($this->getContainer()->getParameter('database_driver') != 'pdo_mysql')
            throw new \Exception('This script only supports mysql databases');

        if ($input->hasArgument('backupName') && $input->getArgument('backupName') != "") {
            $zip_path = $backup_dir . '/' . $input->getArgument('backupName') . '.tar.gz';
        } else {
            $zip_path = $backup_dir . '/'.date('Y-m-d_His') . '.tar.gz';
        }

        $sql_path = tempnam($backup_dir, 'Sql');
        $database_name = $this->getContainer()->getParameter('database_name');
        $database_user = $this->getContainer()->getParameter('database_user');
        $database_password = $this->getContainer()->getParameter('database_password');
        $this->executeShellCommand("mysqldump -u $database_user -p$database_password $database_name > $sql_path",
          'Saving SQL file');

        $output->writeln('Creating archive at '.$zip_path);

        $thumbnail_dir = $this->getContainer()->getParameter('catrobat.thumbnail.dir');
        $screenshot_dir = $this->getContainer()->getParameter('catrobat.screenshot.dir');
        $featuredimage_dir = $this->getContainer()->getParameter('catrobat.featuredimage.dir');
        $programs_dir = $this->getContainer()->getParameter('catrobat.file.storage.dir');
        $mediapackage_dir = $this->getContainer()->getParameter('catrobat.mediapackage.dir');

        $this->executeShellCommand("tar --exclude=.gitignore --mode=0777 --transform \"s|web/resources||\" --transform \"s|" . substr($sql_path, 1) . "|database.sql|\" -zcvf $zip_path $sql_path $thumbnail_dir $screenshot_dir $featuredimage_dir $programs_dir $mediapackage_dir",
          "Create tar.gz file");
        chmod($zip_path, 0777);

        unlink($sql_path);
        $this->output->writeln('Finished! Backupfile created at '.$zip_path);
    }

    private function executeShellCommand($command, $description)
    {
        $this->output->write($description." ('".$command."') ... ");
        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();
        if ($process->isSuccessful()) {
            $this->output->writeln('OK');
            return true;
        }

        $this->output->writeln('failed!');
        return false;
    }
}
