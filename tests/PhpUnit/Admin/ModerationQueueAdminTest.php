<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Admin;

use App\Admin\Moderation\ModerationQueueAdmin;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\Project\Project;
use App\DB\Entity\User\User;
use App\Moderation\ContentVisibilityManager;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\ProjectManager;
use App\Storage\ScreenshotRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(ModerationQueueAdmin::class)]
#[Group('unit')]
class ModerationQueueAdminTest extends TestCase
{
  private string $dir;

  #[\Override]
  protected function setUp(): void
  {
    $this->dir = sys_get_temp_dir().'/moderation-queue-test-'.bin2hex(random_bytes(4)).'/';
    $filesystem = new Filesystem();
    $filesystem->dumpFile($this->dir.'code.xml', "<program>\n<object name=\"Cat\">Hello player</object>\n<object name=\"Dog\">n\u{200B}sfw</object>\n</program>");
    $filesystem->dumpFile($this->dir.'Scene 1/automatic_screenshot.png', 'x');
    $filesystem->dumpFile($this->dir.'images/flagged look.png', 'x');
    $filesystem->dumpFile($this->dir.'images/other.png', 'x');
    $filesystem->dumpFile($this->dir.'sounds/meow.mp3', 'x');
  }

  #[\Override]
  protected function tearDown(): void
  {
    (new Filesystem())->remove($this->dir);
  }

  public function testOlderReportsAreReadFromTheNote(): void
  {
    $report = new ContentReport();
    $report->setNote('Automatic: image "a.png" has NSFW score 0.99; image "b b.png" has NSFW score 0.975');
    $this->assertSame([
      ['type' => 'image', 'name' => 'a.png', 'score' => 0.99],
      ['type' => 'image', 'name' => 'b b.png', 'score' => 0.975],
    ], ModerationQueueAdmin::flaggedAssets($report));

    $report->setNote('Automatic: adults-only signal "18+", "nsfw"; the profile texts with it were cleared');
    $this->assertSame([['type' => 'text', 'name' => '18+'], ['type' => 'text', 'name' => 'nsfw']], ModerationQueueAdmin::flaggedAssets($report));

    $report->setNote('spam, look: image "x.png" has NSFW score 1'); // a reporter's note is no flag
    $this->assertSame([], ModerationQueueAdmin::flaggedAssets($report));

    $report->setNote('Automatic: image "x.png" has NSFW score 1');
    $report->setReporter(new User());
    $this->assertSame([], ModerationQueueAdmin::flaggedAssets($report), 'not even when it looks automatic');
  }

  public function testFlaggedImagesAndTextsComeFirstAndAreNotRepeated(): void
  {
    $report = new ContentReport();
    $report->setContentType('project');
    $report->setContentId('p1');
    $report->setFlaggedAssets([
      ['type' => 'image', 'name' => '../../flagged look.png', 'score' => 0.993],
      ['type' => 'image', 'name' => 'screen_p1.png', 'score' => 0.98],
      ['type' => 'image', 'name' => 'automatic_screenshot.png', 'score' => 0.98],
      ['type' => 'image', 'name' => 'manual_screenshot.png', 'score' => 0.98],
      ['type' => 'text', 'name' => '18+'],
      ['type' => 'text', 'name' => 'only'],
      ['type' => 'text', 'name' => 'game', 'field' => 'name'],
      ['type' => 'text', 'name' => 'player', 'field' => 'description'], // in the code only: not highlighted there
      ['type' => 'text', 'name' => 'nsfw'],
      ['type' => 'text', 'name' => ''],
    ]);

    $assets = $this->admin()->getReviewAssets($report);

    $this->assertSame([
      ['type' => 'image', 'name' => 'flagged look.png', 'score' => 0.993, 'url' => '/resources/extract/p1/images/flagged%20look.png', 'label' => null, 'text' => null],
      ['type' => 'image', 'name' => 'screen_p1.png', 'score' => 0.98, 'url' => '/resources/screenshots/screen_p1.png', 'label' => null, 'text' => null],
      ['type' => 'image', 'name' => 'automatic_screenshot.png', 'score' => 0.98, 'url' => '/resources/extract/p1/Scene%201/automatic_screenshot.png', 'label' => null, 'text' => null],
      ['type' => 'image', 'name' => 'manual_screenshot.png', 'score' => 0.98, 'url' => null, 'label' => null, 'text' => null],
      ['type' => 'text', 'name' => '18+', 'score' => null, 'url' => null, 'label' => 'Description', 'text' => 'Only for 18+'],
      ['type' => 'text', 'name' => 'only', 'score' => null, 'url' => null, 'label' => 'Description', 'text' => 'Only for 18+'],
      ['type' => 'text', 'name' => 'game', 'score' => null, 'url' => null, 'label' => 'Name', 'text' => 'Space game'],
      ['type' => 'text', 'name' => 'player', 'score' => null, 'url' => null, 'label' => null, 'text' => null],
      ['type' => 'text', 'name' => 'nsfw', 'score' => null, 'url' => null, 'label' => 'Code', 'text' => "n\u{200B}sfw"],
    ], $assets['flagged']);
    $this->assertSame(['/resources/extract/p1/images/other.png'], $assets['images']);
    $this->assertSame(['/resources/extract/p1/sounds/meow.mp3'], $assets['sounds']);
    $this->assertSame([['label' => 'Code', 'text' => 'Hello player']], $assets['texts']);
  }

  private function admin(): ModerationQueueAdmin
  {
    $project = new Project();
    $project->setName('Space game');
    $project->setDescription('Only for 18+');

    $project_manager = $this->createStub(ProjectManager::class);
    $project_manager->method('find')->willReturn($project);
    $extracted_file_repository = $this->createStub(ExtractedFileRepository::class);
    $extracted_file_repository->method('loadProjectExtractedFile')->willReturn(new ExtractedCatrobatFile($this->dir, 'resources/extract/p1/', 'p1'));
    $screenshot_repository = $this->createStub(ScreenshotRepository::class);
    $screenshot_repository->method('getScreenshotWebPath')->willReturn('resources/screenshots/screen_p1.png');

    return new ModerationQueueAdmin(
      $this->createStub(ContentVisibilityManager::class),
      $project_manager,
      $extracted_file_repository,
      $screenshot_repository,
    );
  }
}
