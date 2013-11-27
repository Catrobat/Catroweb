<?php

namespace Catrobat\TestBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Catrobat\CatrowebBundle\Services\CatrobatFileExtractor;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Finder\Finder;

class GenerateTestDataCommand extends Command
{
  protected $filesystem;
  protected $source;
  protected $target_directory;
  protected $extractor;
  protected $extracted_source_project_directory;

  public function __construct(Filesystem $filesystem, CatrobatFileExtractor $extractor, $source, $target_directory)
  {
    parent::__construct();
    $this->filesystem = $filesystem;
    $this->source = $source;
    $this->target_directory = realpath($target_directory)."/";
    $this->extractor = $extractor;
  }

  protected function configure()
  {
    $this->setName('catrobat:test:generate')->setDescription('Generates test data');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $dialog = $this->getHelperSet()->get('dialog');
    
    if ($dialog->askConfirmation($output, '<question>Generate test data in ' . $this->target_directory . ' (Y/N)?</question>', false))
    {
      $output->writeln("<info>Reseting directory " . $this->target_directory . "</info>");
      
      $finder = new Finder();
      $finder->in($this->target_directory)->ignoreDotFiles(true)->depth(0);
      foreach ($finder as $file)
      {
        $this->filesystem->remove($file);
      }
      
      $output->writeln("<info>Generating test data</info>");
      $this->extractBaseTestProject("base");
      $this->generateProjectWithExtraImage("project_with_extra_image");
      $this->generateProjectWithMissingImage("project_with_missing_image");
      $output->writeln("Done");
    }
  }

  protected function extractBaseTestProject($directory)
  {
    
    $extracted = $this->extractor->extract(new File($this->source));
    $extracted_path = $extracted->getPath();
    $this->extracted_source_project_directory = $this->target_directory.$directory;
    $this->filesystem->rename($extracted_path, $this->extracted_source_project_directory,true);
  }
  
  protected function generateProjectWithExtraImage($directory)
  {
    $this->filesystem->mirror($this->extracted_source_project_directory, $this->target_directory.$directory);
    $this->filesystem->copy($this->target_directory.$directory."/images/e72ab0fa5902dc9dbd3adbbe558d4727_look.png", $this->target_directory.$directory."/images/e72ab0fa5902dc9dbd3adbbe558d4727_extra.png");
  }
  
  protected function generateProjectWithMissingImage($directory)
  {
    $this->filesystem->mirror($this->extracted_source_project_directory, $this->target_directory.$directory);
    $this->filesystem->remove($this->target_directory.$directory."/images/e72ab0fa5902dc9dbd3adbbe558d4727_look.png", $this->target_directory.$directory."/images/e72ab0fa5902dc9dbd3adbbe558d4727_extra.png");
  }

}
