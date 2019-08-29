<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Services\CatrobatFileCompressor;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Catrobat\Services\CatrobatFileExtractor;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Console\Question\ConfirmationQuestion;


/**
 * Class GenerateTestDataCommand
 * @package App\Catrobat\Commands
 */
class GenerateTestDataCommand extends Command
{
  /**
   * @var Filesystem
   */
  protected $filesystem;
  /**
   * @var
   */
  protected $source;

  /**
   * @var string
   */
  protected $target_directory;
  /**
   * @var CatrobatFileExtractor
   */
  protected $extractor;
  /**
   * @var
   */
  protected $extracted_source_program_directory;
  /**
   * @var CatrobatFileCompressor
   */
  protected $compressor;

  /**
   * GenerateTestDataCommand constructor.
   *
   * @param Filesystem             $filesystem
   * @param CatrobatFileExtractor  $extractor
   * @param CatrobatFileCompressor $compressor
   * @param                        $source
   * @param                        $target_directory
   * @param                        $source_extensions
   */
  public function __construct(Filesystem $filesystem, CatrobatFileExtractor $extractor,
                              CatrobatFileCompressor $compressor, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->filesystem = $filesystem;
    $this->source = $parameter_bag->get('catrobat.test.directory.source');
    $this->target_directory = realpath($parameter_bag->get('catrobat.test.directory.target')) . '/';
    $this->extractor = $extractor;
    $this->compressor = $compressor;
  }

  /**
   *
   */
  protected function configure()
  {
    $this
      ->setName('catrobat:test:generate')
      ->setDescription('Generates test data');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $dialog = $this->getHelper('question');
    $question = new ConfirmationQuestion('<question>Generate test data in ' . $this->target_directory . ' (Y/n)?</question>', true);

    if ($dialog->ask($input, $output, $question))
    {
      $output->writeln('<info>Deleting old test data in ' . $this->target_directory . '</info>');

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
        $this->compressor->compress($this->target_directory . $dir->getRelativePathname(), $this->target_directory, $dir->getRelativePathname());
      }
      $output->writeln('<info>Test data generated</info>');
    }
  }

  /**
   * @param $directory
   */
  protected function extractBaseTestProgram($directory)
  {
    $extracted = $this->extractor->extract(new File($this->source . "test.catrobat"));
    $extracted_path = $extracted->getPath();
    $this->extracted_source_program_directory = $this->target_directory . $directory;
    $this->filesystem->rename($extracted_path, $this->extracted_source_program_directory, true);
  }

  /**
   * @param $directory
   */
  protected function extractEmbroideryTestProgram($directory)
  {
    $extracted = $this->extractor->extract(new File($this->source . "embroidery.catrobat"));
    $extracted_path = $extracted->getPath();
    $this->extracted_source_program_directory = $this->target_directory . $directory;
    $this->filesystem->rename($extracted_path, $this->extracted_source_program_directory, true);
  }

  /**
   * @param $directory
   */
  protected function extractExtensionTestProgram($directory)
  {
    $extracted = $this->extractor->extract(new File($this->source . "extensions.catrobat"));
    $extracted_path = $extracted->getPath();
    $extracted_source_program_directory = $this->target_directory . $directory;
    $this->filesystem->rename($extracted_path, $extracted_source_program_directory, true);
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithExtraImage($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $this->filesystem->copy($this->target_directory . $directory . '/images/6153c44ce0f49f21facbb8c2b2263ce8_Aussehen.png', $this->target_directory . $directory . '/images/6153c44ce0f49f21facbb8c2b2263ce8_extra.png');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithMissingImage($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $this->filesystem->remove($this->target_directory . $directory . '/images/6153c44ce0f49f21facbb8c2b2263ce8_Aussehen.png');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithTooManyFiles($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $this->filesystem->copy($this->target_directory . $directory . '/code.xml', $this->target_directory . $directory . '/extraFile.xml');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithTooManyFolders($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $this->filesystem->mirror($this->target_directory . $directory . '/sounds', $this->target_directory . $directory . '/extraFolder');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithMissingCodeXML($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $this->filesystem->remove($this->target_directory . $directory . '/code.xml');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithInvalidCodeXML($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $this->filesystem->remove($this->target_directory . $directory . '/code.xml');
    $this->filesystem->copy($this->target_directory . $directory . '/automatic_screenshot.png', $this->target_directory . $directory . '/code.xml');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithManualScreenshot($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $this->filesystem->rename($this->target_directory . $directory . '/automatic_screenshot.png', $this->target_directory . $directory . '/manual_screenshot.png');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithScreenshot($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $this->filesystem->rename($this->target_directory . $directory . '/automatic_screenshot.png', $this->target_directory . $directory . '/screenshot.png');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithRudeWordInDescription($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $properties = @simplexml_load_file($this->target_directory . $directory . '/code.xml');
    $properties->header->description = 'FUCK YOU';
    $properties->asXML($this->target_directory . $directory . '/code.xml');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithRudeWordInName($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $properties = @simplexml_load_file($this->target_directory . $directory . '/code.xml');
    $properties->header->programName = 'FUCK YOU';
    $properties->asXML($this->target_directory . $directory . '/code.xml');
  }

  /**
   * @param $directory
   */
  protected function generateProgramWithTags($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    $properties = @simplexml_load_file($this->target_directory . $directory . '/code.xml');
    $properties->header->tags = 'Games,Story';
    $properties->asXML($this->target_directory . $directory . '/code.xml');
  }

  /**
   * @param $directory
   */
  protected function generatePhiroProgram($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    file_put_contents($this->target_directory . $directory . '/permissions.txt', "TEXT_TO_SPEECH\nBLUETOOTH_PHIRO\nVIBRATOR");
  }

  /**
   * @param $directory
   */
  protected function generateLegoProgram($directory)
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory . $directory);
    file_put_contents($this->target_directory . $directory . '/permissions.txt', "TEXT_TO_SPEECH\nBLUETOOTH_LEGO_NXT\nVIBRATOR");
  }
}
