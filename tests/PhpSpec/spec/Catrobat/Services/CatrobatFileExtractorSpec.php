<?php

namespace tests\PhpSpec\spec\App\Catrobat\Services;

use PhpSpec\ObjectBehavior;
use Symfony\Component\HttpFoundation\File\File;

class CatrobatFileExtractorSpec extends ObjectBehavior
{
  public function let()
  {
    $this->beConstructedWith(__SPEC_CACHE_DIR__, '/webpath');
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('App\Catrobat\Services\CatrobatFileExtractor');
  }

  public function it_throws_an_exception_if_given_an_valid_extraction_directory()
  {
    $this->shouldThrow('App\Catrobat\Exceptions\InvalidStorageDirectoryException')->during('__construct', [
      __DIR__ . '/invalid_directory/', '/webpath', 'hash',
    ]);
  }

  public function it_extracts_a_valid_file()
  {
    $valid_catrobat_file = new File(__SPEC_FIXTURES_DIR__ . '/test.catrobat');
    $extracted_file = $this->extract($valid_catrobat_file);
    $extracted_file->shouldHaveType('App\Catrobat\Services\ExtractedCatrobatFile');
  }

  public function it_throws_an_exception_while_extracting_an_invalid_file()
  {
    $invalid_catrobat_file = new File(__SPEC_FIXTURES_DIR__ . '/invalid_archive.catrobat');
    $this->shouldThrow('App\Catrobat\Exceptions\InvalidCatrobatFileException')->duringExtract($invalid_catrobat_file);
  }
}
