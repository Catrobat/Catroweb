<?php

namespace Tests\phpUnit\Catrobat\Services;

use App\Catrobat\Exceptions\InvalidStorageDirectoryException;
use App\Catrobat\Services\CatrobatFileCompressor;
use App\Catrobat\Services\ExtractedCatrobatFile;
use App\Catrobat\Services\ProgramFileRepository;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;
use Tests\phpUnit\Hook\RefreshTestEnvHook;

/**
 * @internal
 * @covers  \App\Catrobat\Services\ProgramFileRepository
 */
class ProgramFileRepositoryTest extends TestCase
{
  private string $storage_dir;

  private string $extract_dir;

  private Filesystem $filesystem;

  private ProgramFileRepository $program_file_repository;

  protected function setUp(): void
  {
    $this->storage_dir = RefreshTestEnvHook::$CACHE_DIR.'zip/';
    $this->extract_dir = RefreshTestEnvHook::$CACHE_DIR.'extract/';
    $this->filesystem = new Filesystem();
    $this->filesystem->mkdir($this->storage_dir);
    $this->filesystem->mkdir($this->extract_dir);
    $this->filesystem->mkdir($this->storage_dir.'tmp/');
    $this->program_file_repository = new ProgramFileRepository($this->storage_dir, $this->extract_dir, new CatrobatFileCompressor());
  }

  protected function tearDown(): void
  {
    $this->filesystem->remove($this->storage_dir);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProgramFileRepository::class, $this->program_file_repository);
  }

  public function testThrowsAnExceptionIfDirectoryIsNotFound(): void
  {
    $this->expectException(InvalidStorageDirectoryException::class);
    $file_compressor = $this->createMock(CatrobatFileCompressor::class);
    $this->program_file_repository->__construct(__DIR__.'/invalid_directory/', $this->extract_dir, $file_compressor);
  }

  public function testThrowsAnExceptionIfDirectoryIsNotFound2(): void
  {
    $this->expectException(InvalidStorageDirectoryException::class);
    $file_compressor = $this->createMock(CatrobatFileCompressor::class);
    $this->program_file_repository->__construct($this->storage_dir, __DIR__.'/invalid_directory/', $file_compressor);
  }

  public function testStoresAFileToTheGivenDirectory(): void
  {
    $file_name = RefreshTestEnvHook::$FIXTURES_DIR.'test.catrobat';
    $id = 'test';
    $file = new File($file_name);

    $this->program_file_repository->saveProjectZipFile($file, $id);

    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($this->storage_dir)->count());
  }

  public function testSavesAGivenProgramDirectory(): void
  {
    $extracted_program = new ExtractedCatrobatFile(RefreshTestEnvHook::$GENERATED_FIXTURES_DIR.'base/', '/webpath', 'hash');
    $id = 'test';

    $this->program_file_repository->zipProject($extracted_program, $id);

    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($this->storage_dir)->count());
  }

  public function testReturnsTheFile(): void
  {
    $file_name = RefreshTestEnvHook::$FIXTURES_DIR.'test.catrobat';
    $id = 'test';
    $file = new File($file_name);

    $this->program_file_repository->saveProjectZipFile($file, $id);

    $original_md5_sum = md5_file($file);
    $returned_file = $this->program_file_repository->getProjectZipFile($id);
    $returned_file_md5_sum = md5_file($returned_file);

    Assert::assertEquals($returned_file_md5_sum, $original_md5_sum);
  }
}
