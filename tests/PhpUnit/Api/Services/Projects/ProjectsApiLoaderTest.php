<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Api\Services\Projects;

use App\Api\Services\Projects\ProjectsApiLoader;
use App\DB\EntityRepository\Project\ExtensionRepository;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\DB\EntityRepository\Project\TagRepository;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Project\CatrobatFile\ProjectZipReconstructor;
use App\Project\CodeStatistics\CodeStatisticsService;
use App\Project\ProjectManager;
use App\Project\ProjectSearchService;
use App\Storage\FileHelper;
use App\System\Testing\PhpUnit\Extension\BootstrapExtension;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * @internal
 */
#[CoversClass(ProjectsApiLoader::class)]
class ProjectsApiLoaderTest extends TestCase
{
  private string $extract_dir;

  private Stub&ProjectFileRepository $file_repository;

  private Stub&ExtractedFileRepository $extracted_file_repository;

  private Stub&ProjectZipReconstructor $reconstructor;

  #[\Override]
  protected function setUp(): void
  {
    $this->extract_dir = BootstrapExtension::$CACHE_DIR.'loader_extract/';
    new Filesystem()->mkdir($this->extract_dir);

    $this->file_repository = $this->createStub(ProjectFileRepository::class);
    $this->extracted_file_repository = $this->createStub(ExtractedFileRepository::class);
    $this->extracted_file_repository->method('getBaseDir')->willReturnCallback(fn (string $id): string => $this->extract_dir.$id.'/');
    $this->reconstructor = $this->createStub(ProjectZipReconstructor::class);
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function tearDown(): void
  {
    FileHelper::removeDirectory($this->extract_dir);
  }

  #[Group('unit')]
  public function testMissingProjectFilesThrowAndLogOneWarning(): void
  {
    $this->file_repository->method('checkIfProjectZipFileExists')->willReturn(false);
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('warning')->with($this->anything(), ['project_id' => 'gone']);
    $logger->expects($this->never())->method('error');

    $this->expectException(FileNotFoundException::class);
    $this->loader($logger)->getProjectCatrobatZipFile('gone');
  }

  #[Group('unit')]
  public function testExistingZipIsReturned(): void
  {
    $zip = $this->extract_dir.'existing.catrobat';
    file_put_contents($zip, 'zip');
    $this->file_repository->method('checkIfProjectZipFileExists')->willReturn(true);
    $this->file_repository->method('getProjectZipFile')->willReturn(new File($zip));

    $file = $this->loader($this->createStub(LoggerInterface::class))->getProjectCatrobatZipFile('existing');

    $this->assertInstanceOf(File::class, $file);
    $this->assertSame($zip, $file->getPathname());
  }

  #[Group('unit')]
  public function testUnexpectedFailureLogsErrorWithException(): void
  {
    new Filesystem()->mkdir($this->extract_dir.'broken');
    $failure = new \RuntimeException('zip failed');
    $this->file_repository->method('checkIfProjectZipFileExists')->willReturn(false);
    $this->file_repository->method('zipProject')->willThrowException($failure);
    $this->reconstructor->method('reconstruct')->willReturn(null);
    $logger = $this->createMock(LoggerInterface::class);
    $logger->expects($this->once())->method('error')->with($this->anything(), ['project_id' => 'broken', 'exception' => $failure]);
    $logger->expects($this->never())->method('warning');

    $this->assertNull($this->loader($logger)->getProjectCatrobatZipFile('broken'));
  }

  private function loader(LoggerInterface $logger): ProjectsApiLoader
  {
    return new ProjectsApiLoader(
      $this->createStub(ProjectManager::class),
      $this->createStub(ProjectSearchService::class),
      $this->createStub(FeaturedRepository::class),
      $this->createStub(TagRepository::class),
      $this->createStub(ExtensionRepository::class),
      new RequestStack(),
      $this->createStub(CodeStatisticsService::class),
      $this->file_repository,
      $this->extracted_file_repository,
      $logger,
      $this->reconstructor,
    );
  }
}
