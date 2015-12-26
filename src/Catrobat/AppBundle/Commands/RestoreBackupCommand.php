<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
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
            ->addArgument('file', InputArgument::REQUIRED, 'Backupfile (*.zip)');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $backupfile = realpath($input->getArgument('file'));
        if (!is_file($backupfile)) {
            throw new \Exception('File not found');
        }

        $phar = new \PharData($backupfile);

        if ($this->getContainer()->getParameter('database_driver') != 'pdo_mysql') {
            throw new \Exception('This script only supports mysql databases');
        }

<<<<<<< HEAD
<<<<<<< HEAD
        $this->executeSymfonyCommand('catrobat:purge', array('--force' => true), $output);
=======
        $command = new PurgeCommand();
        $command->setContainer($this->getContainer());
        try
        {
            $return = $command->run(new ArrayInput(array('--force' => true)),new NullOutput());
            if($return == 0)
            {
                $output->writeln('Purge Command OK');
            }
        }
        catch (\Exception $e) {
            $output->writeln('Something went wrong: ' . $e->getMessage());
        }

        //if ($this->getApplication() == null)
        //    $this->setApplication(new Application());

        //$this->executeSymfonyCommand('catrobat:purge', array('--force' => true), $output);
>>>>>>> 19a5a3f... WEB-194_Backup-CreateAndDownload Added Behat tests for the Backup create, download and restore. Added the mediapackage folder to Backup create and restore.
=======
        $this->executeSymfonyCommand('catrobat:purge', array('--force' => true), $output);
>>>>>>> 0cefb7e... WEB-247 Removed DownloadBackup branch. Merged with origin dev-master.

        $sqlpath = tempnam(sys_get_temp_dir(), 'Sql');
        copy('phar://'.$backupfile.'/database.sql', $sqlpath);
        $databasename = $this->getContainer()->getParameter('database_name');
        $databaseuser = $this->getContainer()->getParameter('database_user');
        $databasepassword = $this->getContainer()->getParameter('database_password');
        $this->executeShellCommand("mysql -u $databaseuser -p$databasepassword $databasename < $sqlpath", 'Saving SQL file');
        unlink($sqlpath);

        $output->writeln('Importing files');

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

<<<<<<< HEAD
<<<<<<< HEAD
=======
        $progress->setMessage("Extracting Media Package");
        $progress->advance();
        $filesystem->mirror("phar://$backupfile/mediapackage/", $this->getContainer()->getParameter('catrobat.mediapackage.dir'));

>>>>>>> 19a5a3f... WEB-194_Backup-CreateAndDownload Added Behat tests for the Backup create, download and restore. Added the mediapackage folder to Backup create and restore.
=======
>>>>>>> 0cefb7e... WEB-247 Removed DownloadBackup branch. Merged with origin dev-master.
        $progress->finish();
        $output->writeln('');

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p SET p.apk_status = :status WHERE p.apk_status != :status");
        $query->setParameter('status', Program::APK_NONE);
        $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p SET p.directory_hash = :hash WHERE p.directory_hash != :hash");
        $query->setParameter('hash', "null");
        $result = $query->getSingleScalarResult();
        $output->writeln('Reset the apk status of '.$result.' projects');
        $output->writeln('Import finished!');
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
        } else {
            $this->output->writeln('failed!');

            return false;
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
