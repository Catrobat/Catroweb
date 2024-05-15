<?php

declare(strict_types=1);

namespace App\System\Commands\ImportProjects;

use App\DB\Entity\User\User;
use App\Project\AddProjectRequest;
use App\Project\CatrobatFile\InvalidCatrobatFileException;
use App\Project\Remix\RemixGraphLayout;
use App\System\Commands\Helpers\RemixManipulationProjectManager;
use App\User\UserManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(name: 'catrobat:import', description: 'Import programs from a given directory to the application')]
class ProgramImportCommand extends Command
{
  final public const string REMIX_GRAPH_NO_LAYOUT = '0';

  public function __construct(private readonly UserManager $user_manager, private readonly RemixManipulationProjectManager $remix_manipulation_program_manager)
  {
    parent::__construct();
  }

  protected function configure(): void
  {
    $this
      ->addArgument('directory', InputArgument::REQUIRED,
        'Directory containing catrobat files for import')
      ->addArgument('user', InputArgument::REQUIRED,
        'User who will be the owner of these programs')
      ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED,
        'Generates remix graph based on given layout',
        self::REMIX_GRAPH_NO_LAYOUT)
    ;
  }

  /**
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $directory = $input->getArgument('directory');
    $username = $input->getArgument('user');
    $layout_idx = intval($input->getOption('remix-layout') - 1);

    $all_layouts = RemixGraphLayout::$REMIX_GRAPH_MAPPING;
    $num_of_layouts = count($all_layouts);
    $remix_graph_mapping = (($layout_idx >= 0) && ($layout_idx < $num_of_layouts)) ? $all_layouts[$layout_idx] : [];

    $this->remix_manipulation_program_manager->useRemixManipulationFileExtractor($remix_graph_mapping);

    $finder = new Finder();
    $finder->files()->name('*.catrobat')->in($directory)->depth(0);

    if (0 == $finder->count()) {
      $output->writeln('No catrobat files found');

      return 1;
    }

    /** @var User|null $user */
    $user = $this->user_manager->findUserByUsername($username);
    if (null == $user) {
      $output->writeln('User '.$username.' was not found!');

      return 1;
    }

    /** @var SplFileInfo $file */
    foreach ($finder as $file) {
      try {
        $output->writeln('Importing file '.$file);
        $add_program_request = new AddProjectRequest($user, new File($file->__toString()));
        $program = $this->remix_manipulation_program_manager->addProject($add_program_request);
        $program->setViews(random_int(0, 10));
        $output->writeln('Added program <'.$program->getName().'> for user: <'.$username.'>');
      } catch (InvalidCatrobatFileException $e) {
        $output->writeln('FAILED to add program!');
        $output->writeln($e->getMessage().' ('.$e->getCode().')');
      }
    }

    return 0;
  }
}
