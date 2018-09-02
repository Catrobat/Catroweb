<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Process\Process;
use Catrobat\AppBundle\Commands\Helpers\CommandHelper;

class CreateBackupCommand extends ContainerAwareCommand
{
  public $output;

  protected function configure()
  {
    $this->setName('catrobat:backup:create')
      ->setDescription('Generates a backup')
      ->addArgument('backupName', InputArgument::OPTIONAL, 'Backupname without extension');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;

    $progress = new ProgressBar($this->output, 7);
    $progress->setFormat(" %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% \n %message%");
    $progress->setMessage('Starting...');
    $progress->setOverwrite(true);
    $progress->start();

    $backup_dir = realpath($this->getContainer()->getParameter('catrobat.backup.dir'));

    $progress->setMessage('Using backup directory ' . $backup_dir);

    if ($this->getContainer()->getParameter('database_driver') != 'pdo_mysql')
    {
      $progress->setMessage('Error: This script only supports mysql databases');
      $progress->finish();

      throw new \Exception('This script only supports mysql databases');
    }
    $progress->advance();

    if ($input->hasArgument('backupName') && $input->getArgument('backupName') != "")
    {
      $zip_path = $backup_dir . '/' . $input->getArgument('backupName') . '.tar.gz';
    }
    else
    {
      $zip_path = $backup_dir . '/' . date('Y-m-d_His') . '.tar.gz';
    }
    $progress->advance();
    $progress->setMessage('Database driver set, Outputpath specified as ' . $zip_path);

    $sql_path = @tempnam($backup_dir, 'Sql');
    $database_name = $this->getContainer()->getParameter('database_name');
    $database_user = $this->getContainer()->getParameter('database_user');
    $database_password = $this->getContainer()->getParameter('database_password');
    $progress->setMessage('Saving SQL file');

    CommandHelper::executeShellCommand("mysqldump -u $database_user -p$database_password $database_name > $sql_path",
      ['timeout' => 14400]);

    $progress->advance();
    $progress->setMessage('Database dump completed.' . " Creating archive at " . $zip_path);

    $thumbnail_dir = $this->getContainer()->getParameter('catrobat.thumbnail.dir');
    $screenshot_dir = $this->getContainer()->getParameter('catrobat.screenshot.dir');
    $featuredimage_dir = $this->getContainer()->getParameter('catrobat.featuredimage.dir');
    $programs_dir = $this->getContainer()->getParameter('catrobat.file.storage.dir');
    $mediapackage_dir = $this->getContainer()->getParameter('catrobat.mediapackage.dir');
    $template_dir = $this->getContainer()->getParameter('catrobat.template.dir');

    $progress->advance();
    $progress->setMessage('Compression started');

    CommandHelper::executeShellCommand("tar --exclude=.gitignore --mode=0777 --transform \"s|web/resources||\" --transform \"s|" . substr($sql_path, 1) . "|database.sql|\" -zcvf $zip_path $sql_path $thumbnail_dir $screenshot_dir $featuredimage_dir $programs_dir $mediapackage_dir $template_dir",
      ['timeout' => 14400]);
    $progress->advance();
    $progress->setMessage('Compression finished. Setting permissions.');

    CommandHelper::executeShellCommand("chmod 777 " . $zip_path, []);
    $progress->advance();
    $progress->setMessage('Permissions set.');


    unlink($sql_path);
    $progress->setMessage("Temp sql file deleted. Finished!\n Backupfile created at " . $zip_path . "\n");
    $progress->finish();

  }
}
