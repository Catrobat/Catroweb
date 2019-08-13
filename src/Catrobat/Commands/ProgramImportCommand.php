<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\RemixManipulationProgramManager;
use App\Catrobat\RemixGraph\RemixGraphLayout;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use App\Entity\UserManager;
use App\Catrobat\Requests\AddProgramRequest;
use Symfony\Component\HttpFoundation\File\File;
use App\Catrobat\Exceptions\InvalidCatrobatFileException;


/**
 * Class ProgramImportCommand
 * @package App\Catrobat\Commands
 */
class ProgramImportCommand extends Command
{
  const REMIX_GRAPH_NO_LAYOUT = 0;

  /**
   * @var Filesystem
   */
  private $file_system;

  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * @var RemixManipulationProgramManager
   */
  private $remix_manipulation_program_manager;

  /**
   * ProgramImportCommand constructor.
   *
   * @param Filesystem                      $filesystem
   * @param UserManager                     $user_manager
   * @param RemixManipulationProgramManager $program_manager
   */
  public function __construct(Filesystem $filesystem, UserManager $user_manager,
                                 RemixManipulationProgramManager $program_manager)
  {
    parent::__construct();
    $this->file_system = $filesystem;
    $this->user_manager = $user_manager;
    $this->remix_manipulation_program_manager = $program_manager;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:import')
      ->setDescription('Import programs from a given directory to the application')
      ->addArgument('directory', InputArgument::REQUIRED, 'Directory containing catrobat files for import')
      ->addArgument('user', InputArgument::REQUIRED, 'User who will be the owner of these programs')
      ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED, 'Generates remix graph based on given layout',
        self::REMIX_GRAPH_NO_LAYOUT);
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $directory = $input->getArgument('directory');
    $username = $input->getArgument('user');
    $layout_idx = ($input->getOption('remix-layout') - 1);

    $all_layouts = RemixGraphLayout::$REMIX_GRAPH_MAPPING;
    $num_of_layouts = count($all_layouts);
    $remix_graph_mapping = (($layout_idx >= 0) && ($layout_idx < $num_of_layouts)) ? $all_layouts[$layout_idx] : [];

    $this->remix_manipulation_program_manager->useRemixManipulationFileExtractor($remix_graph_mapping);

    $finder = new Finder();
    $finder->files()->name('*.catrobat')->in($directory)->depth(0);

    if ($finder->count() == 0)
    {
      $output->writeln('No catrobat files found');

      return;
    }

    $user = $this->user_manager->findUserByUsername($username);
    if ($user == null)
    {
      $output->writeln('User ' . $username . ' was not found!');

      return;
    }

    foreach ($finder as $file)
    {
      try
      {
        $output->writeln('Importing file ' . $file);
        $add_program_request = new AddProgramRequest($user, new File($file));
        $program = $this->remix_manipulation_program_manager->addProgram($add_program_request);
        $output->writeln('Added Program <' . $program->getName() . '>');
      } catch (InvalidCatrobatFileException $e)
      {
        $output->writeln('FAILED to add program!');
        $output->writeln($e->getMessage() . ' (' . $e->getCode() . ')');
      }
    }
  }
}
