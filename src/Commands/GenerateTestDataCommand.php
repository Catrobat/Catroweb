<?php

namespace App\Commands;

use App\Catrobat\Services\CatrobatFileCompressor;
use App\Catrobat\Services\CatrobatFileExtractor;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

class GenerateTestDataCommand extends Command
{
  protected static $defaultName = 'catrobat:test:generate';
  protected Filesystem $filesystem;

  protected string $source;

  protected string $target_directory;

  protected CatrobatFileExtractor $extractor;

  protected string $extracted_source_program_directory;

  protected CatrobatFileCompressor $compressor;

  public function __construct(Filesystem $filesystem, CatrobatFileExtractor $extractor,
                              CatrobatFileCompressor $compressor, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->filesystem = $filesystem;
    $this->source = $parameter_bag->get('catrobat.test.directory.source');
    $this->target_directory = realpath($parameter_bag->get('catrobat.test.directory.target')).'/';
    $this->extractor = $extractor;
    $this->compressor = $compressor;
  }

  protected function configure(): void
  {
    $this
      ->setName('catrobat:test:generate')
      ->setDescription('Generates test data')
      ->addOption('force')
    ;
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $dialog = $this->getHelper('question');
    $question = new ConfirmationQuestion('<question>Generate test data in '.$this->target_directory.' (Y/n)?</question>', true);

    if ($input->getOption('force') || $dialog->ask($input, $output, $question))
    {
      $output->writeln('<info>Deleting old test data in '.$this->target_directory.'</info>');

      $finder = new Finder();
      $finder->in($this->target_directory)->ignoreDotFiles(true)->depth(0);
      foreach ($finder as $file)
      {
        $this->filesystem->remove($file);
      }

      $output->writeln('<info>Generating new test data</info>');
      $this->extractEmbroideryTestProgram('embroidery');
      $this->extractBaseTestProgram('base');
      $this->extractExtensionTestProgram('program_with_extensions');
      $this->generateProgramWithExtraImage('program_with_extra_image');
      $this->generateProgramWithMissingImage('program_with_missing_image');
      $this->generateProgramWithTooManyFiles('program_with_too_many_files');
      $this->generateProgramWithTooManyFolders('program_with_too_many_folders');
      $this->generateProgramWithMissingCodeXML('program_with_missing_code_xml');
      $this->generateProgramWithInvalidCodeXML('program_with_invalid_code_xml');
      $this->generateProgramWithManualScreenshot('program_with_manual_screenshot');
      $this->generateProgramWithScreenshot('program_with_screenshot');
      // $this->generateProgramWithInvalidContentCodeXML("program_with_invalid_content_code_xml");
      $this->generateProgramWithRudeWordInDescription('program_with_rudeword_in_description');
      $this->generateProgramWithTags('program_with_tags');
      $this->generateProgramWithRudeWordInName('program_with_rudeword_in_name');
      $this->generatePhiroProgram('phiro');
      $this->generateLegoProgram('lego');

      $finder->directories()->in($this->target_directory)->depth(0);
      foreach ($finder as $dir)
      {
        $this->compressor->compress($this->target_directory.$dir->getRelativePathname(), $this->target_directory, $dir->getRelativePathname());
      }
      $output->writeln('<info>Test data generated</info>');
    }

    return 0;
  }

  /**
   * @throws Exception
   */
  protected function extractBaseTestProgram(string $directory): void
  {
    $extracted = $this->extractor->extract(new File($this->source.'test.catrobat'));
    $extracted_path = $extracted->getPath();
    $this->extracted_source_program_directory = $this->target_directory.$directory;
    $this->filesystem->rename($extracted_path, $this->extracted_source_program_directory, true);
  }

  /**
   * @throws Exception
   */
  protected function extractEmbroideryTestProgram(string $directory): void
  {
    $extracted = $this->extractor->extract(new File($this->source.'embroidery.catrobat'));
    $extracted_path = $extracted->getPath();
    $this->extracted_source_program_directory = $this->target_directory.$directory;
    $this->filesystem->rename($extracted_path, $this->extracted_source_program_directory, true);
  }

  /**
   * @throws Exception
   */
  protected function extractExtensionTestProgram(string $directory): void
  {
    $extracted = $this->extractor->extract(new File($this->source.'extensions.catrobat'));
    $extracted_path = $extracted->getPath();
    $extracted_source_program_directory = $this->target_directory.$directory;
    $this->filesystem->rename($extracted_path, $extracted_source_program_directory, true);
  }

  protected function generateProgramWithExtraImage(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $this->filesystem->copy($this->target_directory.$directory.'/images/6153c44ce0f49f21facbb8c2b2263ce8_Aussehen.png', $this->target_directory.$directory.'/images/6153c44ce0f49f21facbb8c2b2263ce8_extra.png');
  }

  protected function generateProgramWithMissingImage(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $this->filesystem->remove($this->target_directory.$directory.'/images/6153c44ce0f49f21facbb8c2b2263ce8_Aussehen.png');
  }

  protected function generateProgramWithTooManyFiles(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $this->filesystem->copy($this->target_directory.$directory.'/code.xml', $this->target_directory.$directory.'/extraFile.xml');
  }

  protected function generateProgramWithTooManyFolders(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $this->filesystem->mirror($this->target_directory.$directory.'/sounds', $this->target_directory.$directory.'/extraFolder');
  }

  protected function generateProgramWithMissingCodeXML(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $this->filesystem->remove($this->target_directory.$directory.'/code.xml');
  }

  protected function generateProgramWithInvalidCodeXML(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $this->filesystem->remove($this->target_directory.$directory.'/code.xml');
    $this->filesystem->copy($this->target_directory.$directory.'/automatic_screenshot.png', $this->target_directory.$directory.'/code.xml');
  }

  protected function generateProgramWithManualScreenshot(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $this->filesystem->rename($this->target_directory.$directory.'/automatic_screenshot.png', $this->target_directory.$directory.'/manual_screenshot.png');
  }

  protected function generateProgramWithScreenshot(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $this->filesystem->rename($this->target_directory.$directory.'/automatic_screenshot.png', $this->target_directory.$directory.'/screenshot.png');
  }

  protected function generateProgramWithRudeWordInDescription(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $properties = @simplexml_load_file($this->target_directory.$directory.'/code.xml');
    $properties->header->description = 'FUCK YOU';
    $properties->asXML($this->target_directory.$directory.'/code.xml');
  }

  protected function generateProgramWithRudeWordInName(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $properties = @simplexml_load_file($this->target_directory.$directory.'/code.xml');
    $properties->header->programName = 'FUCK YOU';
    $properties->asXML($this->target_directory.$directory.'/code.xml');
  }

  protected function generateProgramWithTags(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $properties = @simplexml_load_file($this->target_directory.$directory.'/code.xml');
    $properties->header->tags = 'Games,Story';
    $properties->asXML($this->target_directory.$directory.'/code.xml');
  }

  protected function generatePhiroProgram(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    file_put_contents($this->target_directory.$directory.'/permissions.txt', "TEXT_TO_SPEECH\nBLUETOOTH_PHIRO\nVIBRATOR");
  }

  protected function generateLegoProgram(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    file_put_contents($this->target_directory.$directory.'/permissions.txt', "TEXT_TO_SPEECH\nBLUETOOTH_LEGO_NXT\nVIBRATOR");
  }
}
