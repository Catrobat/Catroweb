<?php

namespace Catrobat\AdminBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Catrobat\CoreBundle\Model\ProjectManager;
use Catrobat\CoreBundle\Model\UserManager;
use Catrobat\CoreBundle\Model\Requests\AddProjectRequest;
use Symfony\Component\HttpFoundation\File\File;

class ProjectImportCommand extends Command
{
  private $fileystem;
  private $user_manager;
  private $project_manager;

  public function __construct(Filesystem $filesystem, UserManager $user_manager, ProjectManager $project_manager)
  {
    parent::__construct();
    $this->fileystem = $filesystem;
    $this->user_manager = $user_manager;
    $this->project_manager = $project_manager;
  }

  protected function configure()
  {
    $this->setName('catrobat:import')
          ->setDescription('Import projects from a given directory to the application')
          ->addArgument('directory', InputArgument::REQUIRED, 'Direcory contaning catrobat files for import')
          ->addArgument('user', InputArgument::REQUIRED, 'User who will be the ower of these projects');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $text = "TODO: implement this command";
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
      $add_project_request = new AddProjectRequest($user, new File($file));
      $project = $this->project_manager->addProject($add_project_request);
      $output->writeln("Added Project " . $project->getName());
    }
  }

}