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
#[CoversClass(\App\Security\SecurityHeadersSubscriber::class)]
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

  public function testXmlIsAnInertAttachmentAndSvgIsASandboxedPreview(): void
  {
    $xml = $this->files->asset('one', 'code.xml');
    self::assertNotNull($xml);
    self::assertSame('application/octet-stream', $xml->headers->get('Content-Type'));
    self::assertStringStartsWith('attachment;', (string) $xml->headers->get('Content-Disposition'));
    $svg = $this->files->asset('one', 'images/active.svg');
    self::assertNotNull($svg);
    self::assertSame('image/svg+xml', $svg->headers->get('Content-Type'));
    self::assertStringStartsWith('inline;', (string) $svg->headers->get('Content-Disposition'));
    self::assertSame("sandbox; default-src 'none'; style-src 'unsafe-inline'; img-src data:", $svg->headers->get('Content-Security-Policy'));
    self::assertSame('nosniff', $svg->headers->get('X-Content-Type-Options'));
    $svg->prepare(Request::create('/project-files/one/assets/images/active.svg', server: ['PROJECT_FILE_ACCEL' => '1']));
    self::assertFalse($svg->headers->has('X-Accel-Redirect'));
    ob_start();
    $svg->sendContent();
    self::assertSame(file_get_contents($svg->getFile()->getPathname()), ob_get_clean());
    self::assertSame("sandbox; default-src 'none'; style-src 'unsafe-inline'; img-src data:", $svg->headers->get('Content-Security-Policy'));
    $event = new \Symfony\Component\HttpKernel\Event\ResponseEvent(
      $this->createStub(\Symfony\Component\HttpKernel\HttpKernelInterface::class),
      Request::create('/project-files/one/assets/images/active.svg'),
      \Symfony\Component\HttpKernel\HttpKernelInterface::MAIN_REQUEST,
      $svg,
    );
    new \App\Security\SecurityHeadersSubscriber('prod')->onKernelResponse($event);
    $policies = $svg->headers->all('Content-Security-Policy');
    self::assertCount(2, $policies);
    self::assertSame("sandbox; default-src 'none'; style-src 'unsafe-inline'; img-src data:", $policies[0]);
    self::assertIsString($policies[1]);
    self::assertStringContainsString("default-src 'self'", $policies[1]);
    self::assertSame('DENY', $svg->headers->get('X-Frame-Options'));
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

  public function testClientAudioFormatsUseDetectedCanonicalTypesRegardlessOfFilename(): void
  {
    $formats = [
      'wav' => 'audio/wav', 'mp3' => 'audio/mpeg', 'ogg' => 'audio/ogg',
      'm4a' => 'audio/mp4', 'mp4' => 'audio/mp4', 'aac' => 'audio/aac', 'flac' => 'audio/flac',
    ];
    foreach ($formats as $extension => $expected_mime) {
      foreach (['recording', 'fallback.wav', 'imported.'.$extension, 'misnamed.html'] as $filename) {
        $fixture = __DIR__.'/../../TestData/ProtectedProjectFiles/tone.'.$extension;
        copy($fixture, $this->root.'/extract/one/sounds/'.$filename);
        $response = $this->files->asset('one', 'sounds/'.$filename);
        self::assertNotNull($response, $extension.' content named '.$filename);
        self::assertSame($expected_mime, $response->headers->get('Content-Type'));
        self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
        self::assertStringStartsWith('inline;', (string) $response->headers->get('Content-Disposition'));
        self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
        $response->prepare(Request::create('/project-files/one/assets/sounds/'.$filename));
        ob_start();
        $response->sendContent();
        self::assertSame(file_get_contents($fixture), ob_get_clean());
      }
    }
  }

  public function testSceneRecordingsAlsoSupportPrivateRangesAndAcceleration(): void
  {
    mkdir($this->root.'/extract/one/Scene/sounds', 0o700, true);
    $fixture = __DIR__.'/../../TestData/ProtectedProjectFiles/tone.mp4';
    copy($fixture, $this->root.'/extract/one/Scene/sounds/recording');
    $response = $this->files->asset('one', 'Scene/sounds/recording');
    self::assertNotNull($response);
    $response->prepare(Request::create('/project-files/one/assets/Scene/sounds/recording', server: ['HTTP_RANGE' => 'bytes=0-7']));
    self::assertSame(206, $response->getStatusCode());
    self::assertSame('audio/mp4', $response->headers->get('Content-Type'));
    ob_start();
    $response->sendContent();
    self::assertSame(file_get_contents($fixture, length: 8), ob_get_clean());
    $response = $this->files->asset('one', 'Scene/sounds/recording');
    self::assertNotNull($response);
    $response->prepare(Request::create('/project-files/one/assets/Scene/sounds/recording', server: ['PROJECT_FILE_ACCEL' => '1']));
    self::assertSame('/__protected-project-resources/extract/one/Scene/sounds/recording', $response->headers->get('X-Accel-Redirect'));
    self::assertSame('audio/mp4', $response->headers->get('Content-Type'));
  }

  public function testSoundsRejectActiveContentAndNonAudioEvenWithAudioNames(): void
  {
    foreach (['<html><script>alert(1)</script></html>', '<?php echo "active";', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>', '<?xml version="1.0"?><program/>', 'unrecognized data'] as $content) {
      foreach (['recording', 'fake.wav', 'fake.mp4', 'fake.svg'] as $filename) {
        file_put_contents($this->root.'/extract/one/sounds/'.$filename, $content);
        self::assertNull($this->files->asset('one', 'sounds/'.$filename));
      }
    }
    copy($this->root.'/extract/one/images/look.png', $this->root.'/extract/one/sounds/fake.wav');
    self::assertNull($this->files->asset('one', 'sounds/fake.wav'));
  }

  public function testClientExtensionlessRasterAndGifOrWebpConvertedToPngUseDetectedType(): void
  {
    foreach (['imported', 'converted.webp', 'converted.gif'] as $filename) {
      copy($this->root.'/extract/one/images/look.png', $this->root.'/extract/one/images/'.$filename);
      $response = $this->files->asset('one', 'images/'.$filename);
      self::assertNotNull($response);
      self::assertSame('image/png', $response->headers->get('Content-Type'));
      self::assertSame('nosniff', $response->headers->get('X-Content-Type-Options'));
    }
    copy($this->root.'/extract/one/images/look.png', $this->root.'/extract/one/images/misnamed.jpg');
    self::assertNull($this->files->asset('one', 'images/misnamed.jpg'));
    foreach (['<html><script>alert(1)</script></html>', '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>'] as $content) {
      file_put_contents($this->root.'/extract/one/images/imported', $content);
      self::assertNull($this->files->asset('one', 'images/imported'));
    }
  }

  public function testExtractionUsesTheSharedPerIpDownloadBudgetBeforeReadingAnArchive(): void
  {
    $manager = $this->createStub(\App\Project\ProjectManager::class);
    $manager->method('findProjectIfVisibleToCurrentUser')->willReturn(new \App\DB\Entity\Project\Project());
    $repository = $this->createMock(\App\Project\CatrobatFile\ExtractedFileRepository::class);
    $repository->method('getBaseDir')->willReturn($this->root.'/missing-extract');
    $repository->expects(self::once())->method('loadProjectExtractedFile');
    $limiter = $this->extractionLimiter();
    // An archive request already consumed this caller's budget.
    $limiter->create('192.0.2.1')->consume();
    $controller = new \App\Project\Controller\ProjectFilesController($manager, $repository, $this->files, $limiter);
    $limited = $controller->asset(Request::create('/project-files/one/assets/images/look.png', server: ['REMOTE_ADDR' => '192.0.2.1']), 'one', 'images/look.png');
    self::assertSame(429, $limited->getStatusCode());
    self::assertTrue($limited->headers->hasCacheControlDirective('no-store'));
    self::assertGreaterThan(0, (int) $limited->headers->get('Retry-After'));
    // Another caller has a separate budget and can trigger extraction.
    $allowed = $controller->asset(Request::create('/project-files/one/assets/images/look.png', server: ['REMOTE_ADDR' => '192.0.2.2']), 'one', 'images/look.png');
    self::assertSame(200, $allowed->getStatusCode());
    self::assertFalse($limiter->create('192.0.2.2')->consume()->isAccepted());
  }

  public function testAlreadyExtractedAssetsDoNotConsumeTheExtractionBudget(): void
  {
    $manager = $this->createStub(\App\Project\ProjectManager::class);
    $manager->method('findProjectIfVisibleToCurrentUser')->willReturn(new \App\DB\Entity\Project\Project());
    $repository = $this->createStub(\App\Project\CatrobatFile\ExtractedFileRepository::class);
    $repository->method('getBaseDir')->willReturn($this->root.'/extract/one');
    $limiter = $this->createMock(\Symfony\Component\RateLimiter\RateLimiterFactoryInterface::class);
    $limiter->expects(self::never())->method('create');
    $controller = new \App\Project\Controller\ProjectFilesController($manager, $repository, $this->files, $limiter);
    self::assertSame(200, $controller->asset(Request::create('/project-files/one/assets/images/look.png'), 'one', 'images/look.png')->getStatusCode());
  }

  public function testArchivesKeepExistingByteDeliveryWhenMimeDetectionIsGeneric(): void
  {
    file_put_contents($this->root.'/programs/one.catrobat', 'opaque archive fixture');
    $response = $this->files->archive('one', new BinaryFileResponse($this->root.'/programs/one.catrobat'));
    self::assertNotNull($response);
    self::assertSame('application/zip', $response->headers->get('Content-Type'));
    self::assertStringStartsWith('attachment;', (string) $response->headers->get('Content-Disposition'));
    $response->prepare(Request::create('/project-files/one/archive'));
    ob_start();
    $response->sendContent();
    self::assertSame('opaque archive fixture', ob_get_clean());
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
    $controller = new \App\Project\Controller\ProjectFilesController($manager, $repository, $this->files, $this->extractionLimiter());
    $response = $controller->asset(Request::create('/project-files/one/assets/images/look.png'), 'one', 'images/look.png');
    self::assertSame(404, $response->getStatusCode());
    self::assertTrue($response->headers->hasCacheControlDirective('no-store'));
  }

  public function testBackslashIsAnOrdinaryFilenameCharacter(): void
  {
    copy($this->root.'/extract/one/images/look.png', $this->root.'/extract/one/images/Files\look.png');
    $response = $this->files->asset('one', 'images/Files\look.png');
    self::assertNotNull($response);
    self::assertSame('image/png', $response->headers->get('Content-Type'));
    self::assertStringContainsString('Files_look.png', (string) $response->headers->get('Content-Disposition'));
    self::assertNull($this->files->asset('one', 'images\look.png'));
  }

  public function testEncodedSeparatorsFailBeforeProjectLookup(): void
  {
    $manager = $this->createMock(\App\Project\ProjectManager::class);
    $manager->expects(self::never())->method('findProjectIfVisibleToCurrentUser');
    $repository = $this->createStub(\App\Project\CatrobatFile\ExtractedFileRepository::class);
    $controller = new \App\Project\Controller\ProjectFilesController($manager, $repository, $this->files, $this->extractionLimiter());
    self::assertSame(404, $controller->asset(Request::create('/project-files/one/assets/images%2Flook.png'), 'one', 'images/look.png')->getStatusCode());
  }

  public function testExistingAuthorizedAssetsDoNotTriggerReExtraction(): void
  {
    $manager = $this->createStub(\App\Project\ProjectManager::class);
    $manager->method('findProjectIfVisibleToCurrentUser')->willReturn(new \App\DB\Entity\Project\Project());
    $repository = $this->createMock(\App\Project\CatrobatFile\ExtractedFileRepository::class);
    $repository->expects(self::once())->method('getBaseDir')->with('one')->willReturn($this->root.'/extract/one');
    $repository->expects(self::never())->method('loadProjectExtractedFile');
    $controller = new \App\Project\Controller\ProjectFilesController($manager, $repository, $this->files, $this->extractionLimiter());
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

  private function extractionLimiter(): \Symfony\Component\RateLimiter\RateLimiterFactory
  {
    return new \Symfony\Component\RateLimiter\RateLimiterFactory([
      'id' => 'download_burst', 'policy' => 'fixed_window', 'limit' => 1, 'interval' => '1 minute',
    ], new \Symfony\Component\RateLimiter\Storage\InMemoryStorage());
  }
}
