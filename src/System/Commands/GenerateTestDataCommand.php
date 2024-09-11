<?php

declare(strict_types=1);

namespace App\System\Commands;

use App\Project\CatrobatFile\CatrobatFileCompressor;
use App\Project\CatrobatFile\CatrobatFileExtractor;
use App\Storage\FileHelper;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

#[AsCommand(name: 'catrobat:test:generate', description: 'Generates test data')]
class GenerateTestDataCommand extends Command
{
  protected string $source;

  protected string $target_directory;

  protected string $extracted_source_program_directory;

  public function __construct(protected Filesystem $filesystem, protected CatrobatFileExtractor $extractor,
    protected CatrobatFileCompressor $compressor, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->source = (string) $parameter_bag->get('catrobat.test.directory.source');
    $this->target_directory = (string) $parameter_bag->get('catrobat.test.directory.target');
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('force')
    ;
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    /** @var QuestionHelper $question_helper */
    $question_helper = $this->getHelper('question');
    $question = new ConfirmationQuestion('<question>Generate test data in '.$this->target_directory.' (Y/n)?</question>', true);

    if ($input->getOption('force') || $question_helper->ask($input, $output, $question)) {
      $output->writeln('<info>Deleting old test data in '.$this->target_directory.'</info>');

      $finder = new Finder();
      $output->writeln($this->target_directory);
      FileHelper::emptyDirectory($this->target_directory);

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
      $this->generateProgramWithTags('program_with_tags');
      $this->generatePhiroProgram('phiro');
      $this->generateMindstormsProgram('lego');

      $finder->directories()->in($this->target_directory)->depth(0);
      foreach ($finder as $dir) {
        $this->compressor->compress($this->target_directory.$dir->getRelativePathname(), $this->target_directory, $dir->getRelativePathname());
      }

      $output->writeln('<info>Test data generated</info>');
    }

    return 0;
  }

  /**
   * @throws \Exception
   */
  protected function extractBaseTestProgram(string $directory): void
  {
    $extracted = $this->extractor->extract(new File($this->source.'test.catrobat'));
    $extracted_path = $extracted->getPath();
    $this->extracted_source_program_directory = $this->target_directory.$directory;
    $this->filesystem->rename($extracted_path, $this->extracted_source_program_directory, true);
  }

  /**
   * @throws \Exception
   */
  protected function extractEmbroideryTestProgram(string $directory): void
  {
    $extracted = $this->extractor->extract(new File($this->source.'embroidery.catrobat'));
    $extracted_path = $extracted->getPath();
    $this->extracted_source_program_directory = $this->target_directory.$directory;
    $this->filesystem->rename($extracted_path, $this->extracted_source_program_directory, true);
  }

  /**
   * @throws \Exception
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
    unlink($this->target_directory.$directory.'/images/6153c44ce0f49f21facbb8c2b2263ce8_Aussehen.png');
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
    unlink($this->target_directory.$directory.'/code.xml');
  }

  protected function generateProgramWithInvalidCodeXML(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    unlink($this->target_directory.$directory.'/code.xml');
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

  /**
   * @throws \Exception
   *
   * @psalm-suppress UndefinedPropertyAssignment
   * @psalm-suppress InvalidPropertyFetch
   */
  protected function generateProgramWithTags(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    $properties = @simplexml_load_file($this->target_directory.$directory.'/code.xml');
    $properties->header->tags = 'Games,Story';
    $file_overwritten = $properties->asXML($this->target_directory.$directory.'/code.xml');
    if (!$file_overwritten) {
      throw new \Exception("Can't overwrite code.xml file");
    }
  }

  protected function generatePhiroProgram(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    file_put_contents($this->target_directory.$directory.'/permissions.txt', "TEXT_TO_SPEECH\nBLUETOOTH_PHIRO\nVIBRATOR");
  }

  protected function generateMindstormsProgram(string $directory): void
  {
    $this->filesystem->mirror($this->extracted_source_program_directory, $this->target_directory.$directory);
    file_put_contents($this->target_directory.$directory.'/permissions.txt', "TEXT_TO_SPEECH\nBLUETOOTH_LEGO_NXT\nVIBRATOR");
  }
}
