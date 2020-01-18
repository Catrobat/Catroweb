<?php

namespace App\Catrobat\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


/**
 * Class CleanLogsCommand
 * @package App\Catrobat\Commands
 */
class CleanLogsCommand extends Command
{
  /**
   * @var OutputInterface
   */
  private $output;

  /**
   * @var ParameterBagInterface $parameter_bag
   */
  private $parameter_bag;

  /**
   * CleanApkCommand constructor.
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
    $this->setName('catrobat:clean:logs')
      ->setDescription('Delete the log files');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;
    $this->output->writeln('Deleting log files');
    $log_dir = $this->parameter_bag->get('catrobat.logs.dir');
    $errors = $this->deleteDirectory($log_dir, false);
    if ($errors === 0)
    {
      $this->output->writeln('Successfully deleted log files');

      return 0;
    }
    else
    {
      $this->output->writeln('Could not delete all log files. Please check permissions.');

      return 1;
    }
  }

  private function deleteDirectory(string $dir_path, bool $deleteSelf)
  {
    $errors = 0;
    $objs = array_diff(scandir($dir_path), ['.', '..']);
    foreach ($objs as $obj)
    {
      $file_path = $dir_path . DIRECTORY_SEPARATOR . $obj;
      if (is_dir($file_path))
      {
        $errors += $this->deleteDirectory($file_path, true);
      }
      elseif (unlink($file_path) !== true)
      {
        $errors++;
        $this->output->writeln('Failed removing ' . $file_path);
      }
    }
    if ($deleteSelf && rmdir($dir_path) !== true)
    {
      $errors++;
      $this->output->writeln('Failed removing directory ' . $dir_path);
    }

    return $errors;
  }
} 