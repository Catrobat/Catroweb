<?php

declare(strict_types=1);

namespace App\Moderation;

use App\DB\Entity\Project\Project;
use App\DB\Entity\Studio\Studio;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportCategory;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Security\ContentSafety\ContentSafetyScanner;
use App\Storage\Images\ImageVariantLayout;
use App\Storage\ScreenshotRepository;
use App\Studio\StudioManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Sends a project's screenshots and its largest look images (or a studio's cover) to the NSFW scanner and flags
 * the content for review at the first image at or above the threshold. An image the scanner refuses (4xx) is
 * skipped; scanner down (timeout, 5xx): logged, nothing hidden.
 */
#[AsMessageHandler]
final readonly class ScanContentImagesHandler
{
  /** ponytail: only the largest images of big projects are scanned; raise it if nudity hides in small looks. */
  public const int MAX_IMAGES = 20;

  /** Smaller files are icons or flat shapes. */
  public const int MIN_BYTES = 2_048;

  /** The scanner refuses bodies over 10 MB. */
  public const int MAX_BYTES = 10_000_000;

  public function __construct(
    private EntityManagerInterface $entity_manager,
    private ExtractedFileRepository $extracted_file_repository,
    private ScreenshotRepository $screenshot_repository,
    private StudioManager $studio_manager,
    private ContentSafetyScanner $scanner,
    private AutoModerationService $auto_moderation,
    private LoggerInterface $logger,
  ) {
  }

  public function __invoke(ScanContentImages $message): void
  {
    $this->scan($message, true);
  }

  /**
   * @return string|null what was found (null: all images safe, or the scanner is unavailable); flagged when $flag
   */
  public function scan(ScanContentImages $message, bool $flag): ?string
  {
    $content_type = ContentType::from($message->content_type);
    foreach ($this->imagePaths($content_type, $message->content_id) as $path) {
      $blob = @file_get_contents($path);
      if (false === $blob) {
        continue;
      }

      $result = $this->scanner->scanImageBlob($blob);
      if ($result->invalid) {
        $this->logger->info('Image scan of {type} {id}: scanner refused {image}, skipped', ['type' => $message->content_type, 'id' => $message->content_id, 'image' => basename($path)]);

        continue;
      }

      if ($result->unavailable || $result->skipped) {
        $this->logger->warning('Image scan of {type} {id} stopped: scanner unavailable or disabled', ['type' => $message->content_type, 'id' => $message->content_id]);

        return null;
      }

      if (!$result->safe) {
        $note = sprintf('Automatic: image "%s" has NSFW score %.2f', basename($path), $result->nsfwScore);
        if ($flag) {
          $this->auto_moderation->flagForReview(
            $content_type,
            $message->content_id,
            ContentType::Studio === $content_type ? ReportCategory::InappropriateContent : ReportCategory::SexualContent,
            $note,
            $result->nsfwScore,
          );
        }

        return $note;
      }
    }

    return null;
  }

  /**
   * @return list<string>
   */
  private function imagePaths(ContentType $content_type, string $content_id): array
  {
    if (ContentType::Studio === $content_type) {
      $cover = $this->entity_manager->find(Studio::class, $content_id)?->getCoverAssetPath();
      $path = null === $cover ? null : rtrim($this->studio_manager->getStudioCoverDir(), '/').'/'
        .ImageVariantLayout::filename($cover, ImageVariantLayout::VARIANT_CARD, ImageVariantLayout::DPR_1X, ImageVariantLayout::FORMAT_WEBP);

      return null !== $path && is_file($path) ? [$path] : [];
    }

    $project = $this->entity_manager->find(Project::class, $content_id);
    if (null === $project) {
      return []; // deleted meanwhile
    }

    // The screenshot shown on the site first (it may have been replaced via the API), then the project's own.
    $paths = [$this->screenshot_repository->getScreenshotDir().$this->screenshot_repository->getScreenshotVariantBasename($content_id).'.png'];
    $dir = $this->extracted_file_repository->loadProjectExtractedFile($project)?->getPath();
    $images = [];
    if (null !== $dir && is_dir($dir)) {
      foreach ((new Finder())->files()->in($dir)->name(['manual_screenshot.png', 'automatic_screenshot.png', 'screenshot.png']) as $file) {
        $paths[] = $file->getPathname();
      }

      foreach ((new Finder())->files()->in($dir)->path('~(^|/)images/~') as $file) {
        $images[$file->getPathname()] = $file->getSize();
      }
    }

    arsort($images);
    $paths = array_merge($paths, array_keys($images));

    $paths = array_filter($paths, static function (string $path): bool {
      $size = @filesize($path);

      return false !== $size && $size >= self::MIN_BYTES && $size <= self::MAX_BYTES;
    });

    return array_slice(array_values(array_unique($paths)), 0, self::MAX_IMAGES);
  }
}
