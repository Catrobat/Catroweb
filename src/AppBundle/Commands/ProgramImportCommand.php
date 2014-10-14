<?php

namespace AppBundle\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use AppBundle\Model\ProgramManager;
use AppBundle\Model\UserManager;
use AppBundle\Model\Requests\AddProgramRequest;
use Symfony\Component\HttpFoundation\File\File;
use AppBundle\Exceptions\InvalidCatrobatFileException;

class ProgramImportCommand extends Command
{
  private $fileystem;
  private $user_manager;
  private $program_manager;

  public function __construct(Filesystem $filesystem, UserManager $user_manager, ProgramManager $program_manager)
  {
    parent::__construct();
    $this->fileystem = $filesystem;
    $this->user_manager = $user_manager;
    $this->program_manager = $program_manager;
  }

  protected function configure()
  {
    $this->setName('catrobat:import')
          ->setDescription('Import programs from a given directory to the application')
          ->addArgument('directory', InputArgument::REQUIRED, 'Direcory contaning catrobat files for import')
          ->addArgument('user', InputArgument::REQUIRED, 'User who will be the ower of these programs');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $directory = $input->getArgument('directory');
    $username = $input->getArgument('user');
    
    $finder = new Finder();
    $finder->files()->name('*.catrobat')->in($directory)->depth(0);
    
    if ($finder->count() == 0)
    {
      $output->writeln("No catrobat files found");
      return;
    }
    
    $user = $this->user_manager->findUserByUsername($username);
    if ($user == null)
    {
      $output->writeln("User " . $username . " was not found!");
      return;
    }
    
    foreach ($finder as $file)
    {
      try 
      {
        $output->writeln("Importing file " . $file);
        $add_program_request = new AddProgramRequest($user, new File($file));
        $program = $this->program_manager->addProgram($add_program_request);
        $output->writeln("Added Program " . $program->getName());
      }
      catch (InvalidCatrobatFileException $e)
      {
        $output->writeln("FAILED TO add program " . $program->getName());
        $output->writeln($e->getMessage(). " (" . $e->getCode() . ")");
      }
      
    }
  }

}