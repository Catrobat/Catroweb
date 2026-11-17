<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Moderation;

use App\DB\Entity\Project\Project;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportCategory;
use App\Moderation\AutoModerationService;
use App\Moderation\ScanContentImages;
use App\Moderation\ScanContentImagesHandler;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Security\ContentSafety\ContentSafetyResult;
use App\Security\ContentSafety\ContentSafetyScanner;
use App\Storage\ScreenshotRepository;
use App\Studio\StudioManager;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(ScanContentImagesHandler::class)]
#[Group('unit')]
class ScanContentImagesHandlerTest extends TestCase
{
  private string $dir;

  #[\Override]
  protected function setUp(): void
  {
    $this->dir = sys_get_temp_dir().'/scan-images-test-'.bin2hex(random_bytes(4)).'/';
    $filesystem = new Filesystem();
    $filesystem->dumpFile($this->dir.'code.xml', '<program><header/></program>');
    $filesystem->dumpFile($this->dir.'automatic_screenshot.png', str_repeat('s', 4_000));
    $filesystem->dumpFile($this->dir.'Scene 1/images/big.png', str_repeat('b', 8_000));
    $filesystem->dumpFile($this->dir.'Scene 1/images/mid.png', str_repeat('m', 6_000));
    $filesystem->dumpFile($this->dir.'Scene 1/images/tiny.png', 'tiny');
  }

  #[\Override]
  protected function tearDown(): void
  {
    (new Filesystem())->remove($this->dir);
  }

  public function testOneFlaggedImageQueuesTheProjectWithoutHidingIt(): void
  {
    $scanned = [];
    $scanner = $this->createStub(ContentSafetyScanner::class);
    $scanner->method('scanImageBlob')->willReturnCallback(static function (string $blob) use (&$scanned): ContentSafetyResult {
      $scanned[] = $blob[0];

      return 'b' === $blob[0] ? new ContentSafetyResult(safe: false, nsfwScore: 0.993, label: 'nsfw') : new ContentSafetyResult(safe: true, nsfwScore: 0.01);
    });

    $auto_moderation = $this->createMock(AutoModerationService::class);
    $auto_moderation->expects($this->once())->method('flagForReview')->with(
      ContentType::Project,
      'p1',
      ReportCategory::SexualContent,
      'Automatic: image "big.png" has NSFW score 0.993',
      0.993,
      [['type' => 'image', 'name' => 'big.png', 'score' => 0.993]],
      false,
    );

    $this->handler($scanner, $auto_moderation)(new ScanContentImages('project', 'p1'));

    $this->assertSame(['s', 'b', 'm'], $scanned, 'screenshot first, then all images by size; tiny ones skipped');
  }

  public function testTwoFlaggedImagesHideTheProjectAndAreListedByScore(): void
  {
    $scanner = $this->createStub(ContentSafetyScanner::class);
    $scanner->method('scanImageBlob')->willReturnCallback(static fn (string $blob): ContentSafetyResult => match ($blob[0]) {
      'b' => new ContentSafetyResult(safe: false, nsfwScore: 0.98),
      'm' => new ContentSafetyResult(safe: false, nsfwScore: 0.99),
      default => new ContentSafetyResult(safe: true, nsfwScore: 0.01),
    });

    $auto_moderation = $this->createMock(AutoModerationService::class);
    $auto_moderation->expects($this->once())->method('flagForReview')->with(
      ContentType::Project,
      'p1',
      ReportCategory::SexualContent,
      'Automatic: image "mid.png" has NSFW score 0.990; image "big.png" has NSFW score 0.980',
      0.99,
      [['type' => 'image', 'name' => 'mid.png', 'score' => 0.99], ['type' => 'image', 'name' => 'big.png', 'score' => 0.98]],
      true,
    );

    $this->handler($scanner, $auto_moderation)(new ScanContentImages('project', 'p1'));
  }

  public function testOneImageAtTheHideThresholdHidesTheProject(): void
  {
    $scanner = $this->createStub(ContentSafetyScanner::class);
    $scanner->method('scanImageBlob')->willReturnCallback(static fn (string $blob): ContentSafetyResult => 'm' === $blob[0]
      ? new ContentSafetyResult(safe: false, nsfwScore: 0.996)
      : new ContentSafetyResult(safe: true, nsfwScore: 0.01));

    $auto_moderation = $this->createMock(AutoModerationService::class);
    $auto_moderation->expects($this->once())->method('flagForReview')->with(
      $this->anything(), $this->anything(), $this->anything(), $this->anything(), 0.996, $this->anything(), true,
    );

    $this->handler($scanner, $auto_moderation)(new ScanContentImages('project', 'p1'));
  }

  public function testTheScreenshotsCountAsOneImage(): void
  {
    // The site screenshot is a copy of the project's: both flagged is still one image.
    (new Filesystem())->dumpFile($this->dir.'missing/screen_p1.png', str_repeat('s', 4_000));
    $scanner = $this->createStub(ContentSafetyScanner::class);
    $scanner->method('scanImageBlob')->willReturnCallback(static fn (string $blob): ContentSafetyResult => 's' === $blob[0]
      ? new ContentSafetyResult(safe: false, nsfwScore: 0.99)
      : new ContentSafetyResult(safe: true, nsfwScore: 0.01));

    $auto_moderation = $this->createMock(AutoModerationService::class);
    $auto_moderation->expects($this->once())->method('flagForReview')->with(
      $this->anything(), $this->anything(), $this->anything(), $this->anything(), 0.99, $this->countOf(2), false,
    );

    $this->handler($scanner, $auto_moderation)(new ScanContentImages('project', 'p1'));
  }

  public function testDoesNotHideWhenTheScannerIsUnavailable(): void
  {
    $scanner = $this->createStub(ContentSafetyScanner::class);
    $scanner->method('scanImageBlob')->willReturn(ContentSafetyResult::unavailable());

    $auto_moderation = $this->createMock(AutoModerationService::class);
    $auto_moderation->expects($this->never())->method('flagForReview');

    $this->handler($scanner, $auto_moderation)(new ScanContentImages('project', 'p1'));
  }

  public function testSkipsAnImageTheScannerRefusesAndScansTheNext(): void
  {
    $scanner = $this->createStub(ContentSafetyScanner::class);
    $scanner->method('scanImageBlob')->willReturnCallback(static fn (string $blob): ContentSafetyResult => 's' === $blob[0]
      ? ContentSafetyResult::invalid()
      : new ContentSafetyResult(safe: false, nsfwScore: 0.9));

    $auto_moderation = $this->createMock(AutoModerationService::class);
    $auto_moderation->expects($this->once())->method('flagForReview')->with(
      $this->anything(), $this->anything(), $this->anything(), $this->stringContains('big.png'), $this->anything(), $this->countOf(2), true,
    );

    $this->handler($scanner, $auto_moderation)(new ScanContentImages('project', 'p1'));
  }

  public function testDoesNotHideSafeImages(): void
  {
    $scanner = $this->createStub(ContentSafetyScanner::class);
    $scanner->method('scanImageBlob')->willReturn(new ContentSafetyResult(safe: true, nsfwScore: 0.2));

    $auto_moderation = $this->createMock(AutoModerationService::class);
    $auto_moderation->expects($this->never())->method('flagForReview');

    $this->handler($scanner, $auto_moderation)(new ScanContentImages('project', 'p1'));
  }

  public function testDoesNotHideImagesBelowTheContentThreshold(): void
  {
    // Game art scores high: below the content threshold it is not hidden, even if the avatar threshold would reject it.
    $scanner = $this->createStub(ContentSafetyScanner::class);
    $scanner->method('scanImageBlob')->willReturn(new ContentSafetyResult(safe: false, nsfwScore: 0.96, label: 'nsfw'));

    $auto_moderation = $this->createMock(AutoModerationService::class);
    $auto_moderation->expects($this->never())->method('flagForReview');

    $this->handler($scanner, $auto_moderation, 0.97)(new ScanContentImages('project', 'p1'));
  }

  private function handler(ContentSafetyScanner $scanner, AutoModerationService $auto_moderation, float $threshold = 0.9): ScanContentImagesHandler
  {
    $entity_manager = $this->createStub(EntityManagerInterface::class);
    $entity_manager->method('find')->willReturn(new Project());

    $extracted_file_repository = $this->createStub(ExtractedFileRepository::class);
    $extracted_file_repository->method('loadProjectExtractedFile')->willReturn(new ExtractedCatrobatFile($this->dir, '', 'p1'));

    $screenshot_repository = $this->createStub(ScreenshotRepository::class);
    $screenshot_repository->method('getScreenshotDir')->willReturn($this->dir.'missing/');
    $screenshot_repository->method('getScreenshotVariantBasename')->willReturn('screen_p1');

    return new ScanContentImagesHandler(
      $entity_manager,
      $extracted_file_repository,
      $screenshot_repository,
      $this->createStub(StudioManager::class),
      $scanner,
      $auto_moderation,
      new NullLogger(),
      $threshold,
    );
  }
}
