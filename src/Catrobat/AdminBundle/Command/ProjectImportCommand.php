<?php

namespace Catrobat\AdminBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;

class ProjectImportCommand extends Command
{
	public $fileystem;
	
	
	public function __construct(Filesystem $filesystem)
	{
		parent::__construct();
  	$this->fileystem = $filesystem;
	}

  protected function configure()
  {
    $this->setName('catrobat:import')
    			->setDescription('Import projects from a given directory to the application')
    			->addArgument('directory', InputArgument::REQUIRED, 'Direcory contaning catrobat files for import')
    			->addArgument('user', InputArgument::REQUIRED, 'User who will be the ower of these projects')
    			->addOption('purge', null, InputOption::VALUE_NONE, 'If set, existing projects will be deleted');
  }
  
  protected function execute(InputInterface $input, OutputInterface $output)
  {
  	$text = "TODO: implement this command";
		$directory = $input->getArgument('directory');
  	
  	$finder = new Finder();
  	$finder->files()->in($directory);
  	
    $output->writeln($text);
  }
}