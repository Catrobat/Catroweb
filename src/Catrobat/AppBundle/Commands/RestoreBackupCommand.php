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
    public $debug_output;

    protected function configure()
    {
        $this->setName('catrobat:backup:restore')
            ->setDescription('Restores a backup')
            ->addArgument('file', InputArgument::REQUIRED, 'Backupfile (*.tar.gz)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->debug_output = "";
        $this->output = $output;
        $working_directory = $this->getContainer()->getParameter('catrobat.resources.dir');
        $backup_resources_path = $this->getContainer()->getParameter('catrobat.backup.resources.path');

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

        $this->executeShellCommand("gzip -dc $backup_file | tar -xf - -C $working_directory --wildcards '*.sql'",
          'Extract databse.sql to local resources directory');

        $backup_host_name = $this->getContainer()->getParameter('backup_host_name');
        $backup_host_user = $this->getContainer()->getParameter('backup_host_user');
        $backup_host_password = $this->getContainer()->getParameter('backup_host_password');
        $this->executeShellCommand("gzip -dc $backup_file | sshpass -p '$backup_host_password' ssh $backup_host_user@$backup_host_name tar -xf - -C $backup_resources_path --exclude=database.sql --same-permissions",
          'Extract files to server resources directory');

        $database_name = $this->getContainer()->getParameter('backup_database_name');
        $database_user = $this->getContainer()->getParameter('backup_database_user');
        $database_password = $this->getContainer()->getParameter('backup_database_password');
        $this->executeShellCommand("mysql -u $database_user -p$database_password $database_name < " . $working_directory . "database.sql",
          'Restore SQL file');
        @unlink($working_directory .'database.sql');

        $this->executeShellCommand("mysql -u $database_user -p$database_password $database_name -e 'UPDATE program p SET p.apk_status = " . Program::APK_NONE . " WHERE p.apk_status != " . Program::APK_NONE . "'",
          'Reset the apk status');

        $this->executeShellCommand("mysql -u $database_user -p$database_password $database_name -e 'UPDATE program p SET p.directory_hash = \"null\" WHERE p.directory_hash != \"null\"'",
          'Reset the directory hash');

        /* @var $em \Doctrine\ORM\EntityManager */
        /*$em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p SET p.apk_status = :status WHERE p.apk_status != :status");
        $query->setParameter('status', Program::APK_NONE);
        $result = $query->getSingleScalarResult();
        $this->output->writeln('Reset the apk status of '.$result.' projects');

        $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p SET p.directory_hash = :hash WHERE p.directory_hash != :hash");
        $query->setParameter('hash', "null");
        $result = $query->getSingleScalarResult();
        $this->output->writeln('Reset the directory hash of '.$result.' projects');*/

        $this->output->writeln('Import finished!');
    }

    private function executeShellCommand($command, $description)
    {
        $this->debug_output .= $description." ('".$command."') ... | ";
        $this->output->write($description." ('".$command."') ... ");
        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();
        if ($process->isSuccessful()) {
            $this->output->writeln('OK');
            $this->debug_output .= "OK | ";
            return true;
        }

        throw new \Exception("failed: ". $process->getErrorOutput());
    }

    private function executeSymfonyCommand($command, $args, $output)
    {
        $command = $this->getApplication()->find($command);
        $args['command'] = $command;
        $input = new ArrayInput($args);
        $command->run($input, $output);
    }
}
