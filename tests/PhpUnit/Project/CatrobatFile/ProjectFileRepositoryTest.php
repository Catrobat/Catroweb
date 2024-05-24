<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project\CatrobatFile;

use App\Project\CatrobatFile\CatrobatFileCompressor;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Storage\FileHelper;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Assert;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\File;

/**
 * @internal
 *
 * @covers  \App\Project\CatrobatFile\ProjectFileRepository
 */
class ProjectFileRepositoryTest extends TestCase
{
  private string $storage_dir;

  private string $extract_dir;

  private ProjectFileRepository $program_file_repository;

  #[\Override]
  protected function setUp(): void
  {
    $this->storage_dir = BootstrapExtension::$CACHE_DIR.'zip/';
    $this->extract_dir = BootstrapExtension::$CACHE_DIR.'extract/';
    $filesystem = new Filesystem();
    $filesystem->mkdir($this->storage_dir);
    $filesystem->mkdir($this->extract_dir);
    $filesystem->mkdir($this->storage_dir.'tmp/');

    $this->program_file_repository = new ProjectFileRepository($this->storage_dir, $this->extract_dir, new CatrobatFileCompressor());
  }

  #[\Override]
  protected function tearDown(): void
  {
    FileHelper::removeDirectory($this->storage_dir);
  }

  public function testInitialization(): void
  {
    $this->assertInstanceOf(ProjectFileRepository::class, $this->program_file_repository);
  }

  public function testThrowsAnExceptionIfDirectoryIsNotFound(): void
  {
    $this->expectException(\Exception::class);
    $file_compressor = $this->createMock(CatrobatFileCompressor::class);
    $this->program_file_repository = new ProjectFileRepository(__DIR__.'/invalid_directory/', $this->extract_dir, $file_compressor);
  }

  public function testThrowsAnExceptionIfDirectoryIsNotFound2(): void
  {
    $this->expectException(\Exception::class);
    $file_compressor = $this->createMock(CatrobatFileCompressor::class);
    $this->program_file_repository = new ProjectFileRepository($this->storage_dir, __DIR__.'/invalid_directory/', $file_compressor);
  }

  public function testStoresAFileToTheGivenDirectory(): void
  {
    $file_name = BootstrapExtension::$FIXTURES_DIR.'test.catrobat';
    $id = 'test';
    $file = new File($file_name);

    $this->program_file_repository->saveProjectZipFile($file, $id);

    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($this->storage_dir)->count());
  }

  public function testSavesAGivenProgramDirectory(): void
  {
    $extracted_program = new ExtractedCatrobatFile(BootstrapExtension::$GENERATED_FIXTURES_DIR.'base/', '/webpath', 'hash');
    $id = 'test';

    $this->program_file_repository->zipProject($extracted_program->getPath(), $id);

    $finder = new Finder();
    Assert::assertEquals(1, $finder->files()->in($this->storage_dir)->count());
  }

  public function testReturnsTheFile(): void
  {
    $file_name = BootstrapExtension::$FIXTURES_DIR.'test.catrobat';
    $id = 'test';
    $file = new File($file_name);

    $this->program_file_repository->saveProjectZipFile($file, $id);

    $original_md5_sum = md5_file($file->getRealPath());
    $returned_file = $this->program_file_repository->getProjectZipFile($id);
    $returned_file_md5_sum = md5_file($returned_file->getRealPath());

    Assert::assertEquals($returned_file_md5_sum, $original_md5_sum);
  }
}
