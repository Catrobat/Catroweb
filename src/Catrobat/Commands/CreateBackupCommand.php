<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class CreateBackupCommand.
 */
class CreateBackupCommand extends Command
{
  /**
   * @var
   */
  public $output;

  /**
   * @var ParameterBagInterface
   */
  private $parameter_bag;

  /**
   * CreateBackupCommand constructor.
   */
  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
  }

  protected function configure()
  {
    $this->setName('catrobat:backup:create')
      ->setDescription('Generates a backup')
      ->addArgument('backupName', InputArgument::OPTIONAL, 'Backupname without extension')
    ;
  }

  /**
   * @throws \Exception
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;

    $progress = new ProgressBar($this->output, 7);
    $progress->setFormat(" %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s% \n %message%");
    $progress->setMessage('Starting...');
    $progress->setOverwrite(true);
    $progress->start();

    $backup_dir = realpath($this->parameter_bag->get('catrobat.backup.dir'));

    $progress->setMessage('Using backup directory '.$backup_dir);

    if ('pdo_mysql' !== $_ENV['DATABASE_DRIVER'])
    {
      $progress->setMessage('Error: This script only supports mysql databases');
      $progress->finish();

      throw new \Exception('This script only supports mysql databases');
    }
    $progress->advance();

    if ($input->hasArgument('backupName') && '' != $input->getArgument('backupName'))
    {
      $zip_path = $backup_dir.'/'.$input->getArgument('backupName').'.tar.gz';
    }
    else
    {
      $zip_path = $backup_dir.'/'.date('Y-m-d_His').'.tar.gz';
    }
    $progress->advance();
    $progress->setMessage('Database driver set, Outputpath specified as '.$zip_path);

    $sql_path = @tempnam($backup_dir, 'Sql');
    $database_name = $_ENV['DATABASE_NAME'];
    $database_user = $_ENV['DATABASE_USER'];
    $database_password = $_ENV['DATABASE_PASSWORD'];
    $progress->setMessage('Saving SQL file');

    CommandHelper::executeShellCommand(
      "mysqldump -u {$database_user} -p{$database_password} {$database_name} > {$sql_path}",
      ['timeout' => 14400]
    );

    $progress->advance();
    $progress->setMessage('Database dump completed.'.' Creating archive at '.$zip_path);

    $thumbnail_dir = $this->parameter_bag->get('catrobat.thumbnail.dir');
    $screenshot_dir = $this->parameter_bag->get('catrobat.screenshot.dir');
    $featuredimage_dir = $this->parameter_bag->get('catrobat.featuredimage.dir');
    $programs_dir = $this->parameter_bag->get('catrobat.file.storage.dir');
    $mediapackage_dir = $this->parameter_bag->get('catrobat.mediapackage.dir');
    $template_dir = $this->parameter_bag->get('catrobat.template.dir');

    $progress->advance();
    $progress->setMessage('Compression started');

    CommandHelper::executeShellCommand('tar --exclude=.gitignore --mode=0777 --transform "s|public/resources||" --transform "s|'.substr($sql_path, 1)."|database.sql|\" -cv 
      {$sql_path} {$thumbnail_dir} {$screenshot_dir} {$featuredimage_dir} {$programs_dir} {$mediapackage_dir} {$template_dir} | pigz > {$zip_path}",
      ['timeout' => 14400]);
    $progress->advance();
    $progress->setMessage('Compression finished. Setting permissions.');

    CommandHelper::executeShellCommand('chmod 777 '.$zip_path, []);
    $progress->advance();
    $progress->setMessage('Permissions set.');

    unlink($sql_path);
    $progress->setMessage("Temp sql file deleted. Finished!\n Backupfile created at ".$zip_path."\n");
    $progress->finish();
  }
}
