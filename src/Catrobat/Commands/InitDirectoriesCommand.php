<?php

namespace App\Catrobat\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InitDirectoriesCommand extends Command
{
  // ToDo this command is not working!

  public Filesystem $filesystem;

  public string $extract_directory;

  public string $program_file_directory;

  public string $thumbnail_directory;

  public string $screenshot_directory;

  public string $media_package_directory;
  protected static $defaultName = 'catrobat:init:directories';

  public function __construct(Filesystem $filesystem)
  {
    parent::__construct();
    $this->filesystem = $filesystem;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:init:directories')
      ->setDescription('Creates directories needed by the catroweb application')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    if (null == $this->filesystem)
    {
      $output->writeln('Filesystem not initialized');

      return -1;
    }
    $text = '';
    $text .= $this->makeSureDirectoryExists($this->program_file_directory);
    $text .= $this->makeSureDirectoryExists($this->extract_directory);
    $text .= $this->makeSureDirectoryExists($this->thumbnail_directory);
    $text .= $this->makeSureDirectoryExists($this->screenshot_directory);
    $text .= $this->makeSureDirectoryExists($this->media_package_directory);
    $text .= $this->showInfo();
    $output->writeln($text);

    return 0;
  }

  private function makeSureDirectoryExists(string $directory): string
  {
    $text = '';
    if (!$this->filesystem->exists($directory))
    {
      $this->filesystem->mkdir($directory);
      $text .= 'Creating Directory '.$directory."\n";
    }
    else
    {
      $text .= 'Directory already exists: '.$directory."\n";
    }

    return $text;
  }

  private function showInfo(): string
  {
    return 'Make sure the above directories are writeable by your console and webuser';
  }
}
