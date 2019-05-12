<?php

namespace App\Catrobat\Commands;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class CleanLogsCommand
 * @package App\Catrobat\Commands
 */
class CleanLogsCommand extends ContainerAwareCommand
{
  /**
   * @var OutputInterface
   */
  private $output;

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
    $log_dir = $this->getContainer()->getParameter('catrobat.logs.dir');
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