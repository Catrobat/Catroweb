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
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

/**
 * Sends a project's screenshots and its largest look images (or a studio's cover) to the NSFW scanner and flags
 * the content when images score at or above the threshold: one such image only queues it for review (it stays
 * visible), two or more, or one at or above the hide threshold, hide it until a moderator has looked. The
 * screenshots count as one image (the site's is a copy of the project's). An image the scanner refuses (4xx) is
 * skipped; scanner down (timeout, 5xx): logged, the scan stops and acts on what it found so far.
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
    // Stricter than the avatar threshold: game art (sprites, horror and fan games) scores high on the model.
    #[Autowire('%env(float:CONTENT_SAFETY_CONTENT_THRESHOLD)%')]
    private float $threshold = 0.97,
    // Drawn and anime art reaches 0.99 too (all false positives in production scored 0.988-0.993), so one image
    // below this only queues the content for review.
    #[Autowire('%env(float:CONTENT_SAFETY_HIDE_THRESHOLD)%')]
    private float $hide_threshold = 0.995,
    #[Autowire('%env(int:CONTENT_SAFETY_HIDE_MIN_IMAGES)%')]
    private int $hide_min_images = 2,
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
    $flagged = [];
    $flagged_images = [];
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

        break;
      }

      if ($result->nsfwScore >= $this->threshold) {
        $flagged[] = ['type' => 'image', 'name' => basename($path), 'score' => $result->nsfwScore];
        // Identical looks count once, all screenshots as one.
        $flagged_images[str_contains($path, '/images/') ? md5($blob) : 'screenshot'] = true;
      }
    }

    if ([] === $flagged) {
      return null;
    }

    usort($flagged, static fn (array $a, array $b): int => $b['score'] <=> $a['score']);
    $score = $flagged[0]['score'];
    $hide = count($flagged_images) >= $this->hide_min_images || $score >= $this->hide_threshold;
    $note = 'Automatic: '.implode('; ', array_map(
      static fn (array $image): string => sprintf('image "%s" has NSFW score %.3f', $image['name'], $image['score']),
      $flagged,
    ));
    if ($flag) {
      $this->auto_moderation->flagForReview(
        $content_type,
        $message->content_id,
        ContentType::Studio === $content_type ? ReportCategory::InappropriateContent : ReportCategory::SexualContent,
        $note,
        $score,
        $flagged,
        $hide,
      );
    }

    return $note.($hide ? ' (hide)' : ' (review only)');
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
