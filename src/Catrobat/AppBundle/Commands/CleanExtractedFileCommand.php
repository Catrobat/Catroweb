<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Entity\ProgramManager;
use Catrobat\AppBundle\Services\ExtractedFileRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanExtractedFileCommand extends ContainerAwareCommand
{
  private $extracted_file_repository;
  private $program_manager;
  private $output;

  public function __construct(ExtractedFileRepository $extracted_file_repository, ProgramManager $program_manager)
  {
    parent::__construct();
    $this->extracted_file_repository = $extracted_file_repository;
    $this->program_manager = $program_manager;
  }

  protected function configure()
  {
    $this->setName('catrobat:clean:extracted')
         ->setDescription('Delete the extracted directories');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;
    $programs = $this->program_manager->getProgramsWithExtractedDirectoryHash();
    $this->output->writeln('There are ' . count($programs) . ' extracted directories to delete!');

    foreach ($programs as $program)
    {
      $this->output->writeln('Program with ID: ' . $program->getId() . ', Hash: ' . $program->getExtractedDirectoryHash());
      $this->extracted_file_repository->removeProgramExtractedFile($program);
    }

    $this->output->writeln('All extracted directories deleted!');
    return 0;
  }
} 