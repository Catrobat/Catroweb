<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Filesystem\Filesystem;

class RestoreBackupCommand extends ContainerAwareCommand
{
    public $output;

    protected function configure()
    {
        $this->setName('catrobat:backup:restore')
            ->setDescription('Restores a backup')
            ->addArgument('file', InputArgument::REQUIRED, 'Backupfile (*.tar.gz)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $working_directory = $this->getContainer()->getParameter('catrobat.resources.dir');

        $backup_file = realpath($input->getArgument('file'));
        if (!is_file($backup_file)) {
            $backup_file = realpath($input->getFirstArgument());

            if (!is_file($backup_file))
                throw new \Exception('File not found');
        }
        $this->output->writeln('Backup File: ' . $backup_file);

        if ($this->getContainer()->getParameter('database_driver') != 'pdo_mysql')
            throw new \Exception('This script only supports mysql databases');

        $this->executeSymfonyCommand('catrobat:purge', array('--force' => true), $this->output);

        $this->executeShellCommand("gzip -dc $backup_file | tar -xf - -C $working_directory",
          'Extract files to resources directory');
        $this->executeShellCommand("chmod -R 0777 web/resources/",
          'Set permission for the files');

        $database_name = $this->getContainer()->getParameter('database_name');
        $database_user = $this->getContainer()->getParameter('database_user');
        $database_password = $this->getContainer()->getParameter('database_password');
        $this->executeShellCommand("mysql -u $database_user -p$database_password $database_name < " . $working_directory . "database.sql",
          'Saving SQL file');
        @unlink($working_directory .'database.sql');

        $progress = new ProgressBar($output, 4);
        $progress->setFormat(' %current%/%max% [%bar%] %message%');
        $progress->start();

        $filesystem = new Filesystem();
        
        $progress->setMessage("Extracting Thumbnails");
        $progress->advance();
        $filesystem->mirror("phar://$backupfile/thumbnails/", $this->getContainer()->getParameter('catrobat.thumbnail.dir'));
        
        $progress->setMessage("Extracting Screenshots");
        $progress->advance();
        $filesystem->mirror("phar://$backupfile/screenshots/", $this->getContainer()->getParameter('catrobat.screenshot.dir'));
        
        $progress->setMessage("Extracting Featured Images");
        $progress->advance();
        $filesystem->mirror("phar://$backupfile/featured/", $this->getContainer()->getParameter('catrobat.featuredimage.dir'));
        
        $progress->setMessage("Extracting Programs");
        $progress->advance();
        $filesystem->mirror("phar://$backupfile/programs/", $this->getContainer()->getParameter('catrobat.file.storage.dir'));
        
        $progress->finish();
        $output->writeln('');

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p SET p.apk_status = :status WHERE p.apk_status != :status");
        $query->setParameter('status', Program::APK_NONE);
        $result = $query->getSingleScalarResult();
        $this->output->writeln('Reset the apk status of '.$result.' projects');

        $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p SET p.directory_hash = :hash WHERE p.directory_hash != :hash");
        $query->setParameter('hash', "null");
        $result = $query->getSingleScalarResult();
        $this->output->writeln('Reset the directory hash of '.$result.' projects');

        $this->output->writeln('Import finished!');
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

    private function executeSymfonyCommand($command, $args, $output)
    {
        $command = $this->getApplication()->find($command);
        $args['command'] = $command;
        $input = new ArrayInput($args);
        $command->run($input, $output);
    }
}
