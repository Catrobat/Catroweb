<?php
namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use Doctrine\ORM\EntityManager;
use Catrobat\AppBundle\Entity\Program;

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
        if (! is_file($backupfile)) {
            throw new \Exception('File not found');
        }

        $zip = new \ZipArchive();
        if ($zip->open($backupfile) !== true)
        {
            throw new \Exception('Could not open backup file');
        }
        
        if ($this->getContainer()->getParameter('database_driver') != 'pdo_mysql')
        {
            throw new \Exception('This script only supports mysql databases');
        }
        
        $this->executeSymfonyCommand("catrobat:purge", array("--force" => true), $output);
        
        $sqlpath = tempnam(sys_get_temp_dir(), 'Sql');
        copy("zip://".$backupfile."#database.sql", $sqlpath);
        $databasename = $this->getContainer()->getParameter('database_name');
        $databaseuser = $this->getContainer()->getParameter('database_user');
        $databasepassword = $this->getContainer()->getParameter('database_password');
        $this->executeShellCommand("mysql -u $databaseuser -p$databasepassword $databasename < $sqlpath", "Saving SQL file");
        unlink($sqlpath);
        
        $output->writeln("Importing files");
        
        $mapper = array(
            "thumbnails" => $this->getContainer()->getParameter('catrobat.thumbnail.dir'),
            "screenshots" => $this->getContainer()->getParameter('catrobat.screenshot.dir'),
            "featured" =>  $this->getContainer()->getParameter('catrobat.featuredimage.dir'),
            "programs" => $this->getContainer()->getParameter('catrobat.file.storage.dir')
        );
        
        $progress = new ProgressBar($output, $zip->numFiles);
        $progress->setFormat(' %current%/%max% [%bar%] %message%');
        $progress->start(); 
        
        for($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            
            $progress->setMessage($filename);
            $progress->advance();
            
            $fileinfo = pathinfo($filename);
            foreach ($mapper as $zipdir => $catrobatdir)
            {
                if (preg_match("/".$zipdir."\/(.*)/", $filename) === 1)
                {
                    copy("zip://".$backupfile."#".$filename, $catrobatdir . "/" . $fileinfo['basename']);
                }
            }
        }
        $zip->close();
        $progress->finish();
        $output->writeln("");

        /* @var $em \Doctrine\ORM\EntityManager */
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p SET p.apk_status = :status WHERE p.apk_status != :status");
        $query->setParameter("status", Program::APK_NONE);
        $result = $query->getSingleScalarResult();
        $output->writeln("Reset the apk status of " . $result . " projects");
        $output->writeln("Import finished!");
    }
    
    private function executeShellCommand($command, $description)
    {
        $this->output->write($description." ('".$command."') ... ");
        $process = new Process($command);
        $process->setTimeout(3600);
        $process->run();
        if ($process->isSuccessful()) {
            $this->output->writeln("OK");
    
            return true;
        } else {
            $this->output->writeln("failed!");
    
            return false;
        }
    }
    
    private function executeSymfonyCommand($command, $args, $output)
    {
        $command = $this->getApplication()->find($command);
        $args["command"] = $command;
        $input = new ArrayInput($args);
        $command->run($input, $output);
    }
}