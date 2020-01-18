<?php

namespace App\Catrobat\Commands;

use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


/**
 * Class CleanBackupsCommand
 * @package App\Catrobat\Commands
 */
class CleanBackupsCommand extends Command
{
  /**
   * @var
   */
  private $output;

  /**
   * @var ParameterBagInterface
   */
  private $parameter_bag;

  /**
   * CleanBackupsCommand constructor.
   *
   * @param ParameterBagInterface $parameter_bag
   */
  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
  }


  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:clean:backup')
      ->setDescription('Delete all Backups')
      ->addArgument('backupfile', InputArgument::OPTIONAL, 'backup file (tar.gz)')
      ->addOption('all', null, InputOption::VALUE_NONE, 'all backups are deleted');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|null
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;

    $backupdir = realpath($this->parameter_bag->get('catrobat.backup.dir'));
    if ($input->getOption("all"))
    {
      $files = glob($backupdir . '/*'); // get all file names
      foreach ($files as $file)
      { // iterate files
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        if ($ext == "gz" && is_file($file))
        {
          unlink($file);
        } // delete file
      }
      $this->output->writeln('All backups deleted!');
    }
    else
    {
      if ($input->getArgument("backupfile"))
      {

        $backupfile = $input->getArgument("backupfile");
        $files = scandir($backupdir);
        if (!in_array($backupfile, $files))
        {
          throw new Exception('Backupfile not found.');
        }
        if (is_file($backupdir . "/" . $backupfile))
        {
          unlink($backupdir . "/" . $backupfile);
        }

        $this->output->writeln('Backup deleted!');
      }
      else
      {
        throw new Exception('No Arguments');
      }
    }

    return 0;
  }
} 