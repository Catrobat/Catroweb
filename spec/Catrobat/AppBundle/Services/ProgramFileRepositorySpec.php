<?php

namespace spec\Catrobat\AppBundle\Services;

use Catrobat\AppBundle\Services\ExtractedCatrobatFile;
use Catrobat\AppBundle\Services\CatrobatFileCompressor;
use PhpSpec\ObjectBehavior;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Finder\Finder;

class ProgramFileRepositorySpec extends ObjectBehavior
{
  private $storage_dir;
  private $filesystem;

  public function let()
  {
    $this->storage_dir = __SPEC_CACHE_DIR__ . '/file_repository/';
    $this->filesystem = new Filesystem();
    $this->filesystem->mkdir($this->storage_dir);
    $this->filesystem->mkdir($this->storage_dir . "tmp/");
    $this->beConstructedWith($this->storage_dir, '', new CatrobatFileCompressor(), $this->storage_dir . "tmp/");
  }

  public function it_is_initializable()
  {
    $this->shouldHaveType('Catrobat\AppBundle\Services\ProgramFileRepository');
  }

  public function it_throws_an_exception_if_directory_is_not_found()
  {
    $this->shouldThrow('Catrobat\AppBundle\Exceptions\InvalidStorageDirectoryException')->during('__construct', [__DIR__ . '/invalid_directory/', '', new CatrobatFileCompressor(), $this->storage_dir . "tmp/"]);
  }

  public function it_stores_a_file_to_the_given_directory()
  {
    $file_name = __SPEC_FIXTURES_DIR__ . '/test.catrobat';
    $id = 'test';
    $file = new File($file_name);

    $this->saveProgramfile($file, $id);

    $finder = new Finder();
    expect($finder->files()->in($this->storage_dir)->count())->toBe(1);
  }

  public function it_saves_a_given_program_directory()
  {
    $extracted_program = new ExtractedCatrobatFile(__SPEC_GENERATED_FIXTURES_DIR__ . '/base/', '/webpath', 'hash');
    $id = 'test';

    $this->saveProgram($extracted_program, $id);

    $finder = new Finder();
    expect($finder->files()->in($this->storage_dir)->count())->toBe(1);
  }

  public function it_returns_the_file()
  {
    $file_name = __SPEC_FIXTURES_DIR__ . '/test.catrobat';
    $id = 'test';
    $file = new File($file_name);

    $this->saveProgramfile($file, $id);

    $original_md5_sum = md5_file($file);
    $returned_file = $this->getProgramFile($id)->getWrappedObject();
    $returned_file_md5_sum = md5_file($returned_file);

    expect($returned_file_md5_sum)->toBe($original_md5_sum);
  }

  public function letgo()
  {
    $this->filesystem->remove($this->storage_dir);
  }
}
