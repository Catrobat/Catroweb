<?php
namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Process\Process;

class CreateBackupCommand extends ContainerAwareCommand
{
    public $output;
    public $totalsize;
    
    protected function configure()
    {
        $this->setName('catrobat:backup:create')->setDescription('Generates a backup');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        
        $backupdir = realpath($this->getContainer()->getParameter('catrobat.backup.dir'));
        $output->writeln("Using backup directory " . $backupdir);
        
        if ($this->getContainer()->getParameter('database_driver') != 'pdo_mysql')
        {
            throw new \Exception('This script only supports mysql databases');
        }
        
        $sqlpath = tempnam($backupdir, 'Sql');
        $databasename = $this->getContainer()->getParameter('database_name');
        $databaseuser = $this->getContainer()->getParameter('database_user');
        $databasepassword = $this->getContainer()->getParameter('database_password');
        $this->executeShellCommand("mysqldump -u $databaseuser -p$databasepassword $databasename > $sqlpath", "Saving SQL file");
        
        $zippath = $backupdir . "/" . date("Y-m-d_His") . ".zip";
        $output->writeln("Creating zipfile at " . $zippath);
        $zip = new \ZipArchive();
        $zip->open($zippath, \ZIPARCHIVE::CREATE);
        
        $zip->addFile($sqlpath, "database.sql");
        
        $this->output->writeln("Saving thumbnails");
        $dir = $this->getContainer()->getParameter('catrobat.thumbnail.dir');
        $this->addFilesToArchive($dir, "thumbnails", $zip);
        
        $this->output->writeln("Saving screenshots");
        $dir = $this->getContainer()->getParameter('catrobat.screenshot.dir');
        $this->addFilesToArchive($dir, "screenshots", $zip);

        $this->output->writeln("Saving featured images");
        $dir2 = $this->getContainer()->getParameter('catrobat.featuredimage.dir');
        $this->addFilesToArchive($dir2, "featured", $zip);
        
        $this->output->writeln("Saving catrobat files");
        $dir = $this->getContainer()->getParameter('catrobat.file.storage.dir');
        $this->addFilesToArchive($dir, "programs", $zip);
        
        $this->output->writeln("Saving Zip File");
        $this->output->writeln("Packing " . sprintf("%.2f", $this->totalsize / 1024 / 1024) . " MB, this may take a while...");
        $zip->close();
        chmod($zippath, 0600);
        
        unlink($sqlpath);
        $this->output->writeln("Finished! Backupfile created at " . $zippath);
    }
    
    private function addFilesToArchive($src_directory, $dest_directory, $zip)
    {
        $finder = new Finder();
        $files = $finder->in($src_directory)->files();
        
        $progress = new ProgressBar($this->output, $files->count());
        $progress->setFormat(' %current%/%max% [%bar%] %message%');
        $progress->start();
        
        $size = 0;
        
        foreach ($files as $file)
        {
            $progress->setMessage($file->getFilename());
            $size += $file->getSize();
            $progress->advance();
            $zip->addFile($file->getPathname(), $dest_directory . "/" . $file->getFilename());
        }
        $this->totalsize += $size;
        $progress->setMessage(sprintf("%.2f", $size / 1024 / 1024) . " MB");
        $progress->finish();
        $this->output->writeln("");
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
}