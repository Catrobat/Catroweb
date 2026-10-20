<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Project;

use App\Project\ProtectedProjectFileResponse;
use App\Project\ProtectedProjectFiles;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;

/** @internal */
#[CoversClass(ProtectedProjectFiles::class)]
#[CoversClass(ProtectedProjectFileResponse::class)]
#[CoversClass(\App\Project\Controller\ProjectFilesController::class)]
#[CoversClass(\App\Project\CatrobatFile\ExtractedFileRepository::class)]
#[Group('unit')]
final class ProtectedProjectFilesTest extends TestCase
{
  private string $root;
  private ProtectedProjectFiles $files;

  #[\Override]
  protected function setUp(): void
  {
    $this->root = sys_get_temp_dir().'/catroweb-project-files-'.bin2hex(random_bytes(8));
    mkdir($this->root.'/extract/one/images', 0o700, true);
    mkdir($this->root.'/extract/one/sounds', 0o700, true);
    mkdir($this->root.'/programs', 0o700, true);
    mkdir($this->root.'/private', 0o700, true);
    $png = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a8V0AAAAASUVORK5CYII=', true);
    self::assertIsString($png);
    file_put_contents($this->root.'/extract/one/images/look.png', $png);
    file_put_contents($this->root.'/extract/one/code.xml', '<?xml version="1.0"?><program><header/></program>');
    file_put_contents($this->root.'/extract/one/images/active.svg', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>');
    copy($this->root.'/extract/one/images/look.png', $this->root.'/private/secret.png');
    $zip = new \ZipArchive();
    self::assertTrue($zip->open($this->root.'/programs/one.catrobat', \ZipArchive::CREATE));
    $zip->addFromString('code.xml', '<program/>');
    $zip->close();
    $this->files = new ProtectedProjectFiles($this->root.'/extract/', $this->root.'/programs/');
  }

  #[\Override]
  protected function tearDown(): void
  {
    new Filesystem()->remove($this->root);
  }

  public function testRasterImageIsPrivateAndSupportsFallbackRanges(): void
  {
    $response = $this->files->asset('one', 'images/look.png');
    self::assertNotNull($response);
    $response->prepare(Request::create('/project-files/one/assets/images/look.png', server: ['HTTP_RANGE' => 'bytes=0-7']));
    self::assertSame(206, $response->getStatusCode());
    self::assertSame('8', $response->headers->get('Content-Length'));
    self::assertSame('image/png', $response->headers->get('Content-Type'));
    self::assertTrue($response->headers->hasCacheControlDirective('private'));
    self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
    self::assertFalse($response->headers->hasCacheControlDirective('public'));
    self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    ob_start();
    $response->sendContent();
    self::assertSame("\x89PNG\r\n\x1a\n", ob_get_clean());
  }

  public function testHeadHasTheFileLengthWithoutAResponseBody(): void
  {
    $response = $this->files->asset('one', 'images/look.png');
    self::assertNotNull($response);
    $response->prepare(Request::create('/project-files/one/assets/images/look.png', 'HEAD'));
    self::assertSame((string) filesize($this->root.'/extract/one/images/look.png'), $response->headers->get('Content-Length'));
    ob_start();
    $response->sendContent();
    self::assertSame('', ob_get_clean());
  }

  public function testOnlyTheServerParameterEnablesAcceleration(): void
  {
    $response = $this->files->asset('one', 'images/look.png');
    self::assertNotNull($response);
    $response->prepare(Request::create('/project-files/one/assets/images/look.png', server: [
      'HTTP_PROJECT_FILE_ACCEL' => '1', 'HTTP_X_SENDFILE_TYPE' => 'X-Accel-Redirect', 'HTTP_X_ACCEL_MAPPING' => '/=/_untrusted/',
    ]));
    self::assertFalse($response->headers->has('X-Accel-Redirect'));
    $response = $this->files->asset('one', 'images/look.png');
    self::assertNotNull($response);
    $response->prepare(Request::create('/project-files/one/assets/images/look.png', server: ['PROJECT_FILE_ACCEL' => '1', 'HTTP_RANGE' => 'bytes=0-7']));
    self::assertSame('/__protected-project-resources/extract/one/images/look.png', $response->headers->get('X-Accel-Redirect'));
    self::assertSame(200, $response->getStatusCode());
    self::assertFalse($response->headers->has('Content-Range'));
    ob_start();
    $response->sendContent();
    self::assertSame('', ob_get_clean());
  }

  public function testXmlAndSvgAreAttachmentsWithAnInertContentType(): void
  {
    foreach (['code.xml', 'images/active.svg'] as $path) {
      $response = $this->files->asset('one', $path);
      self::assertNotNull($response);
      self::assertSame('application/octet-stream', $response->headers->get('Content-Type'));
      self::assertStringStartsWith('attachment;', (string) $response->headers->get('Content-Disposition'));
    }
  }

  public function testInvalidPathsAndIdsCannotEscapeTheProjectDirectory(): void
  {
    foreach (['../private/secret.png', 'images/../code.xml', '/images/look.png', 'images//look.png', 'images/%2e%2e/code.xml', 'images%2flook.png', 'images\look.png', "images/look.png\0", 'code.xml/anything'] as $path) {
      self::assertNull($this->files->asset('one', $path));
    }
    foreach (['../one', 'one/../one', 'one%2f', ''] as $id) {
      self::assertNull($this->files->asset($id, 'images/look.png'));
    }
  }

  public function testSymlinksCannotAccessOtherProjectsOrCas(): void
  {
    symlink($this->root.'/private/secret.png', $this->root.'/extract/one/images/escape.png');
    symlink($this->root.'/extract/one', $this->root.'/extract/two');
    self::assertNull($this->files->asset('one', 'images/escape.png'));
    self::assertNull($this->files->asset('two', 'images/look.png'));
    symlink($this->root.'/extract/one/images', $this->root.'/extract/one/Scene');
    self::assertNull($this->files->asset('one', 'Scene/images/look.png'));
  }

  public function testUnsupportedTypesAndMisleadingImageExtensionsAreRejected(): void
  {
    file_put_contents($this->root.'/extract/one/images/script.html', '<script>alert(1)</script>');
    file_put_contents($this->root.'/extract/one/images/fake.png', '<html>not an image</html>');
    self::assertNull($this->files->asset('one', 'images/script.html'));
    self::assertNull($this->files->asset('one', 'images/fake.png'));
    self::assertNull($this->files->asset('one', 'other.xml'));
  }

  public function testArchiveIsBoundToTheRequestedIdAndKeepsItsDisposition(): void
  {
    $original = new BinaryFileResponse($this->root.'/programs/one.catrobat');
    $original->setContentDisposition('attachment', 'My project.catrobat');
    $response = $this->files->archive('one', $original);
    self::assertNotNull($response);
    self::assertSame($original->headers->get('Content-Disposition'), $response->headers->get('Content-Disposition'));
    self::assertTrue($response->headers->hasCacheControlDirective('private'));
    self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
    self::assertFalse($response->headers->hasCacheControlDirective('public'));
    self::assertNull($this->files->archive('two', $original));
    self::assertNull($this->files->archive('one', new BinaryFileResponse($this->root.'/private/secret.png')));
    symlink($this->root.'/programs/one.catrobat', $this->root.'/programs/two.catrobat');
    self::assertNull($this->files->archive('two', new BinaryFileResponse($this->root.'/programs/two.catrobat')));
  }

  public function testDeploymentResourceRootSymlinksRemainSupported(): void
  {
    symlink($this->root.'/extract', $this->root.'/shared-extract');
    symlink($this->root.'/programs', $this->root.'/shared-programs');
    $files = new ProtectedProjectFiles($this->root.'/shared-extract', $this->root.'/shared-programs');
    self::assertNotNull($files->asset('one', 'images/look.png'));
    self::assertNotNull($files->archive('one', new BinaryFileResponse($this->root.'/shared-programs/one.catrobat')));
  }

  public function testDeniedProjectsNeverReadOrReExtractFiles(): void
  {
    $manager = $this->createMock(\App\Project\ProjectManager::class);
    $manager->expects(self::once())->method('findProjectIfVisibleToCurrentUser')->with('one')->willReturn(null);
    $repository = $this->createMock(\App\Project\CatrobatFile\ExtractedFileRepository::class);
    $repository->expects(self::never())->method('loadProjectExtractedFile');
    $controller = new \App\Project\Controller\ProjectFilesController($manager, $repository, $this->files);
    $response = $controller->asset(Request::create('/project-files/one/assets/images/look.png'), 'one', 'images/look.png');
    self::assertSame(404, $response->getStatusCode());
    self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
  }

  public function testEncodedSeparatorsFailBeforeProjectLookup(): void
  {
    $manager = $this->createMock(\App\Project\ProjectManager::class);
    $manager->expects(self::never())->method('findProjectIfVisibleToCurrentUser');
    $repository = $this->createStub(\App\Project\CatrobatFile\ExtractedFileRepository::class);
    $controller = new \App\Project\Controller\ProjectFilesController($manager, $repository, $this->files);
    self::assertSame(404, $controller->asset(Request::create('/project-files/one/assets/images%2Flook.png'), 'one', 'images/look.png')->getStatusCode());
  }

  public function testExistingAuthorizedAssetsDoNotTriggerReExtraction(): void
  {
    $manager = $this->createStub(\App\Project\ProjectManager::class);
    $manager->method('findProjectIfVisibleToCurrentUser')->willReturn(new \App\DB\Entity\Project\Project());
    $repository = $this->createMock(\App\Project\CatrobatFile\ExtractedFileRepository::class);
    $repository->expects(self::once())->method('getBaseDir')->with('one')->willReturn($this->root.'/extract/one');
    $repository->expects(self::never())->method('loadProjectExtractedFile');
    $controller = new \App\Project\Controller\ProjectFilesController($manager, $repository, $this->files);
    $response = $controller->asset(Request::create('/project-files/one/assets/images/look.png'), 'one', 'images/look.png');
    self::assertSame(200, $response->getStatusCode());
    self::assertSame('image/png', $response->headers->get('Content-Type'));
  }

  public function testGeneratedPreviewUrlsUseTheProtectedNamespaceAndFrontControllerBase(): void
  {
    $parameters = new \Symfony\Component\DependencyInjection\ParameterBag\ParameterBag([
      'catrobat.file.extract.dir' => $this->root.'/extract/',
      'catrobat.file.storage.dir' => $this->root.'/programs/',
    ]);
    $requests = new \Symfony\Component\HttpFoundation\RequestStack();
    $requests->push(Request::create('http://example.test/index_test.php/app/project/one', server: [
      'SCRIPT_FILENAME' => '/var/www/catroweb/public/index_test.php',
      'SCRIPT_NAME' => '/index_test.php',
    ]));
    $repository = new \App\Project\CatrobatFile\ExtractedFileRepository(
      $parameters,
      $this->createStub(\App\Project\ProjectManager::class),
      $this->createStub(\App\Project\CatrobatFile\CatrobatFileExtractor::class),
      new \Psr\Log\NullLogger(),
      $requests,
    );
    $project = $this->createStub(\App\DB\Entity\Project\Project::class);
    $project->method('getId')->willReturn('one');
    $extracted = $repository->loadProjectExtractedFile($project);
    self::assertNotNull($extracted);
    self::assertSame('index_test.php/project-files/one/assets/', $extracted->getWebPath());
  }
}
