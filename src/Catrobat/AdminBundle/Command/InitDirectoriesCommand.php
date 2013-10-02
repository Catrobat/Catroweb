<?php
namespace Catrobat\AdminBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class InitDirectoriesCommand extends Command
{
	public $fileystem;
	public $extract_directory;
	public $projectfile_directory;
	public $thumbnail_directory;
	public $screenshot_directory;
	


	public function __construct(Filesystem $filesystem, $projectfile_directory)
	{
		parent::__construct();
		$this->fileystem = $filesystem;
		$this->projectfile_directory = $projectfile_directory;
	}

	protected function configure()
	{
		$this->setName('catrobat:init:directories')
		->setDescription('Creates directories needed by the catroweb application');
	}

	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$text = "";
		if ($this->fileystem == null)
		{
			$output->writeln("Filesystem not initalized");
			return;
		}
		$text = "";
		$text .= $this->makeSuredirectoryExists($this->projectfile_directory);
		$text .= $this->makeSuredirectoryExists($this->extract_directory);
		$text .= $this->makeSuredirectoryExists($this->thumbnail_directory);
		$text .= $this->makeSuredirectoryExists($this->screenshot_directory);
		$text .= $this->showInfo();
		$output->writeln($text);
	}

	private function makeSuredirectoryExists($directory)
	{
		$text = "";
		if (!$this->fileystem->exists($directory))
		{
			$this->fileystem->mkdir($this->dir);
			$this->fileystem->chgrp($this->dir, "www-data");
		}
		else
		{
			$text .= "Directory already exists: " . $directory . "\n";
		}
		return $text;
	}
	
	private function showInfo()
	{
		return "Make sure the above directories are writeable by your console and webuser";
	}
	
	public function setExtractDirectory($extract_directory) {
		$this->extract_directory = $extract_directory;
	}
	
	public function setProjectfileDirectory($projectfile_directory) {
		$this->projectfile_directory = $projectfile_directory;
	}
	
	public function setThumbnailDirectory($thumbnail_directory) {
		$this->thumbnail_directory = $thumbnail_directory;
	}
	
	public function setScreenshotDirectory($screenshot_directory) {
		$this->screenshot_directory = $screenshot_directory;
	}
	
}