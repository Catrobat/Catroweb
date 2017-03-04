<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\RemixGraph\RemixGraphLayout;
use Catrobat\AppBundle\Services\CatrobatFileExtractor;
use Catrobat\AppBundle\Services\RemixUrlIndicator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Entity\UserManager;
use Catrobat\AppBundle\Requests\AddProgramRequest;
use Symfony\Component\HttpFoundation\File\File;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;

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

    public function __construct(Filesystem $filesystem, UserManager $user_manager,
                                RemixManipulationProgramManager $program_manager)
    {
        parent::__construct();
        $this->file_system = $filesystem;
        $this->user_manager = $user_manager;
        $this->remix_manipulation_program_manager = $program_manager;
    }

    protected function configure()
    {
        $this->setName('catrobat:import')
          ->setDescription('Import programs from a given directory to the application')
          ->addArgument('directory', InputArgument::REQUIRED, 'Directory containing catrobat files for import')
          ->addArgument('user', InputArgument::REQUIRED, 'User who will be the owner of these programs')
          ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED, 'Generates remix graph based on given layout',
              self::REMIX_GRAPH_NO_LAYOUT);
    }

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

        if ($finder->count() == 0) {
            $output->writeln('No catrobat files found');
            return;
        }

        $user = $this->user_manager->findUserByUsername($username);
        if ($user == null) {
            $output->writeln('User '.$username.' was not found!');
            return;
        }

        foreach ($finder as $file) {
            try {
                $output->writeln('Importing file '.$file);
                $add_program_request = new AddProgramRequest($user, new File($file));
                $program = $this->remix_manipulation_program_manager->addProgram($add_program_request);
                $output->writeln('Added Program <'.$program->getName().'>');
            } catch (InvalidCatrobatFileException $e) {
                $output->writeln('FAILED to add program!');
                $output->writeln($e->getMessage().' ('.$e->getCode().')');
            }
        }
    }
}

class RemixManipulationProgramManager extends ProgramManager
{
    public function useRemixManipulationFileExtractor($remix_graph_mapping)
    {
        $old_file_extractor = $this->file_extractor;
        $this->file_extractor = new RemixManipulationCatrobatFileExtractor(
            $remix_graph_mapping,
            $old_file_extractor->getExtractDir(),
            $old_file_extractor->getExtractPath()
        );
    }
}

class RemixManipulationCatrobatFileExtractor extends CatrobatFileExtractor
{
    private $current_program_id;
    private $remix_graph_mapping;

    public function __construct($remix_graph_mapping, $extract_dir, $extract_path)
    {
        $this->current_program_id = 1;
        $this->remix_graph_mapping = $remix_graph_mapping;
        parent::__construct($extract_dir, $extract_path);
    }

    public function extract(File $file)
    {
        $extracted_catrobat_file = parent::extract($file);

        $all_parent_program_ids = [];
        foreach ($this->remix_graph_mapping as $parent_program_data => $child_program_ids) {
            if (in_array($this->current_program_id, $child_program_ids)) {
                $all_parent_program_ids[] = explode(',', $parent_program_data);
            }
        }

        $previous_parent_string = '';
        for ($parent_program_index = 0; $parent_program_index < count($all_parent_program_ids); $parent_program_index++) {
            $parent_program_data = $all_parent_program_ids[$parent_program_index];
            $parent_id = $parent_program_data[0];
            $current_parent_url = !$parent_program_data[1]
                                ? '/pocketcode/program/' . $parent_id
                                : 'https://scratch.mit.edu/projects/' . $parent_id . '/';
            $previous_parent_string = $this->generateRemixUrlsStringForMergedProgram($previous_parent_string,
                $current_parent_url);
        }

        $remix_url_string = $previous_parent_string;
        $program_xml_properties = $extracted_catrobat_file->getProgramXmlProperties();

        // NOTE: force using Catrobat language version 0.993 in order to allow multiple parents (see: RemixUpdater.php) {
        $program_xml_properties->header->catrobatLanguageVersion = '0.993';
        // }

        $program_xml_properties->header->remixOf = '';
        $program_xml_properties->header->url = $remix_url_string;
        $extracted_catrobat_file->saveProgramXmlProperties();

        $this->current_program_id++;
        return $extracted_catrobat_file;
    }

    public function generateRemixUrlsStringForMergedProgram($previous_parent_string, $current_parent_url)
    {
        if ($previous_parent_string == '') {
            return $current_parent_url;
        }

        return 'PREVIOUS: '
             . RemixUrlIndicator::PREFIX_INDICATOR . $previous_parent_string . RemixUrlIndicator::SUFFIX_INDICATOR . ', '
             . 'NEXT: '
             . RemixUrlIndicator::PREFIX_INDICATOR . $current_parent_url . RemixUrlIndicator::SUFFIX_INDICATOR;
    }
}
