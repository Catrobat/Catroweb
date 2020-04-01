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
use Tests\phpUnit\Hook\ClearCacheHook;

/**
 * @internal
 * @coversNothing
 */
class ProgramFileRepositoryTest extends TestCase
{
  private string $storage_dir;

  private Filesystem $filesystem;

  private ProgramFileRepository $program_file_repository;

  protected function setUp(): void
  {
    $this->storage_dir = ClearCacheHook::$CACHE_DIR.'file_repository/';
    $this->filesystem = new Filesystem();
    $this->filesystem->mkdir($this->storage_dir);
    $this->filesystem->mkdir($this->storage_dir.'tmp/');
    $this->program_file_repository = new ProgramFileRepository($this->storage_dir, '', new CatrobatFileCompressor(), $this->storage_dir.'tmp/');
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
    $this->program_file_repository->__construct(__DIR__.'/invalid_directory/', '', $file_compressor, '');
  }

  public function testStoresAFileToTheGivenDirectory(): void
  {
    $file_name = ClearCacheHook::$FIXTURES_DIR.'test.catrobat';
    $id = 'test';
    $file = new File($file_name);

    $this->program_file_repository->saveProgramFile($file, $id);

    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($this->storage_dir)->count());
  }

  public function testSavesAGivenProgramDirectory(): void
  {
    $extracted_program = new ExtractedCatrobatFile(ClearCacheHook::$GENERATED_FIXTURES_DIR.'base/', '/webpath', 'hash');
    $id = 'test';

    $this->program_file_repository->saveProgram($extracted_program, $id);

    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($this->storage_dir)->count());
  }

  public function testReturnsTheFile(): void
  {
    $file_name = ClearCacheHook::$FIXTURES_DIR.'test.catrobat';
    $id = 'test';
    $file = new File($file_name);

    $this->program_file_repository->saveProgramFile($file, $id);

    $original_md5_sum = md5_file($file);
    $returned_file = $this->program_file_repository->getProgramFile($id);
    $returned_file_md5_sum = md5_file($returned_file);

    Assert::assertEquals($returned_file_md5_sum, $original_md5_sum);
  }
}
