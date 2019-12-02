<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Services\ProgramFileRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;


/**
 * Class InitDirectoriesCommand
 * @package App\Catrobat\Commands
 */
class InitDirectoriesCommand extends Command
{
  /**
   * @var Filesystem
   */
  public $fileystem;
  /**
   * @var
   */
  public $extract_directory;
  /**
   * @var
   */
  public $programfile_directory;
  /**
   * @var
   */
  public $thumbnail_directory;
  /**
   * @var
   */
  public $screenshot_directory;
  /**
   * @var
   */
  public $mediapackage_directory;

  /**
   * InitDirectoriesCommand constructor.
   *
   * @param Filesystem $filesystem
   * @param            $programfile_directory
   */
  public function __construct(Filesystem $filesystem, ProgramFileRepository $programfile_directory)
  {
    parent::__construct();
    $this->fileystem = $filesystem;
    $this->programfile_directory = $programfile_directory;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:init:directories')
      ->setDescription('Creates directories needed by the catroweb application');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    if ($this->fileystem == null)
    {
      $output->writeln('Filesystem not initalized');

      return;
    }
    $text = '';
    $text .= $this->makeSuredirectoryExists($this->programfile_directory);
    $text .= $this->makeSuredirectoryExists($this->extract_directory);
    $text .= $this->makeSuredirectoryExists($this->thumbnail_directory);
    $text .= $this->makeSuredirectoryExists($this->screenshot_directory);
    $text .= $this->makeSuredirectoryExists($this->mediapackage_directory);
    $text .= $this->showInfo();
    $output->writeln($text);
  }

  /**
   * @param $directory
   *
   * @return string
   */
  private function makeSuredirectoryExists($directory)
  {
    $text = '';
    if (!$this->fileystem->exists($directory))
    {
      $this->fileystem->mkdir($directory);
      $text .= 'Creating Directory ' . $directory . "\n";
    }
    else
    {
      $text .= 'Directory already exists: ' . $directory . "\n";
    }

    return $text;
  }

  /**
   * @return string
   */
  private function showInfo()
  {
    return 'Make sure the above directories are writeable by your console and webuser';
  }

  /**
   * @param $extract_directory
   */
  public function setExtractDirectory($extract_directory)
  {
    $this->extract_directory = $extract_directory;
  }

  /**
   * @param $programfile_directory
   */
  public function setProgramfileDirectory($programfile_directory)
  {
    $this->programfile_directory = $programfile_directory;
  }

  /**
   * @param $thumbnail_directory
   */
  public function setThumbnailDirectory($thumbnail_directory)
  {
    $this->thumbnail_directory = $thumbnail_directory;
  }

  /**
   * @param $screenshot_directory
   */
  public function setScreenshotDirectory($screenshot_directory)
  {
    $this->screenshot_directory = $screenshot_directory;
  }

  /**
   * @param $mediapackage_directory
   */
  public function setMediaPackageDirectory($mediapackage_directory)
  {
    $this->mediapackage_directory = $mediapackage_directory;
  }
}
