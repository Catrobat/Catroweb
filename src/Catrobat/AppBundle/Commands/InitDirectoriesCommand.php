<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;

class InitDirectoriesCommand extends Command
{
    public $fileystem;
    public $extract_directory;
    public $programfile_directory;
    public $thumbnail_directory;
    public $screenshot_directory;
    public $mediapackage_directory;

    public function __construct(Filesystem $filesystem, $programfile_directory)
    {
        parent::__construct();
        $this->fileystem = $filesystem;
        $this->programfile_directory = $programfile_directory;
    }

    protected function configure()
    {
        $this->setName('catrobat:init:directories')
            ->setDescription('Creates directories needed by the catroweb application');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->fileystem == null) {
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

    private function makeSuredirectoryExists($directory)
    {
        $text = '';
        if (!$this->fileystem->exists($directory)) {
            $this->fileystem->mkdir($directory);
            $text .= 'Creating Directory '.$directory."\n";
        } else {
            $text .= 'Directory already exists: '.$directory."\n";
        }

        return $text;
    }

    private function showInfo()
    {
        return 'Make sure the above directories are writeable by your console and webuser';
    }

    public function setExtractDirectory($extract_directory)
    {
        $this->extract_directory = $extract_directory;
    }

    public function setProgramfileDirectory($programfile_directory)
    {
        $this->programfile_directory = $programfile_directory;
    }

    public function setThumbnailDirectory($thumbnail_directory)
    {
        $this->thumbnail_directory = $thumbnail_directory;
    }

    public function setScreenshotDirectory($screenshot_directory)
    {
        $this->screenshot_directory = $screenshot_directory;
    }

    public function setMediaPackageDirectory($mediapackage_directory)
    {
        $this->mediapackage_directory = $mediapackage_directory;
    }
}
