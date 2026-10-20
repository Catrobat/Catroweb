<?php

declare(strict_types=1);

namespace Tests\PhpUnit\System\Commands\Maintenance;

use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\Project\Project;
use App\Project\CatrobatFile\ProjectFileRepository;
use App\Storage\Images\ImageVariantGenerator;
use App\Storage\ScreenshotRepository;
use App\System\Commands\Maintenance\CheckDataConsistencyCommand;
use App\System\Testing\DataFixtures\ProjectDataFixtures;
use App\System\Testing\DataFixtures\UserDataFixtures;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\MockObject\MockObject;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @internal
 */
#[CoversClass(CheckDataConsistencyCommand::class)]
class CheckDataConsistencyCommandTest extends KernelTestCase
{
  private EntityManagerInterface $entity_manager;

  private string $zip_dir;

  private string $extract_dir;

  private string $screenshot_dir;

  private string $thumbnail_dir;

  private ImageVariantGenerator&MockObject $variant_generator;

  /** project with only a zip: healthy file, but no screenshot */
  private string $zip_only_id;

  /** project with only an extracted dir: healthy file */
  private string $extract_only_id;

  /** project without any file, but with a screenshot lacking thumbnail + variants */
  private string $broken_id;

  private string $report_id;

  /** @var list<string> */
  private array $created_paths = [];

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $this->entity_manager = $this->service(EntityManagerInterface::class);
    $this->zip_dir = $this->parameter('catrobat.file.storage.dir');
    $this->extract_dir = $this->parameter('catrobat.file.extract.dir');
    $this->screenshot_dir = $this->parameter('catrobat.screenshot.dir');
    $this->thumbnail_dir = $this->parameter('catrobat.thumbnail.dir');

    $owner = $this->service(UserDataFixtures::class)->insertUser(['name' => 'consistency_'.uniqid()]);
    $fixtures = $this->service(ProjectDataFixtures::class);
    // The fixture gives every project an extracted directory.
    $this->zip_only_id = (string) $fixtures->insertProject(['name' => 'consistency zip only', 'owned by' => $owner->getUsername()])->getId();
    $this->extract_only_id = (string) $fixtures->insertProject(['name' => 'consistency extract only', 'owned by' => $owner->getUsername()])->getId();
    $this->broken_id = (string) $fixtures->insertProject(['name' => 'consistency broken', 'owned by' => $owner->getUsername()])->getId();

    $fs = new Filesystem();
    $fs->remove([$this->extract_dir.$this->zip_only_id, $this->extract_dir.$this->broken_id]);
    $this->created_paths = [
      $this->zip_dir.$this->zip_only_id.'.catrobat',
      $this->extract_dir.$this->extract_only_id,
      $this->screenshot_dir.'screen_'.$this->broken_id.'.png',
      $this->thumbnail_dir.'screen_'.$this->broken_id.'.png',
    ];
    $fs->dumpFile($this->created_paths[0], 'zip');
    $image = new \Imagick();
    $image->newImage(100, 100, 'white');
    $image->writeImage('png:'.$this->created_paths[2]);
    $image->destroy();

    $report = new ContentReport();
    $report->setContentType('project');
    $report->setContentId('00000000-0000-0000-0000-00000000dead');
    $report->setCategory('spam');
    $this->entity_manager->persist($report);
    $this->entity_manager->flush();
    $this->report_id = (string) $report->getId();

    $this->variant_generator = $this->createMock(ImageVariantGenerator::class);
  }

  #[\Override]
  protected function tearDown(): void
  {
    (new Filesystem())->remove($this->created_paths);
    $this->entity_manager->clear();
    foreach ([$this->zip_only_id, $this->extract_only_id, $this->broken_id] as $id) {
      $project = $this->entity_manager->find(Project::class, $id);
      if (null !== $project) {
        $this->entity_manager->remove($project);
      }
    }

    $report = $this->entity_manager->find(ContentReport::class, $this->report_id);
    if (null !== $report) {
      $this->entity_manager->remove($report);
    }

    $this->entity_manager->flush();
    parent::tearDown();
  }

  public function testDryRunReportsWithoutChangingAnything(): void
  {
    $this->variant_generator->expects($this->never())->method('generate');

    $tester = $this->runCommand([]);
    $display = $tester->getDisplay();

    $tester->assertCommandIsSuccessful();
    $this->assertStringContainsString('missing_project_file: '.$this->broken_id, $display);
    $this->assertStringNotContainsString('missing_project_file: '.$this->zip_only_id, $display);
    $this->assertStringNotContainsString('missing_project_file: '.$this->extract_only_id, $display);
    $this->assertStringContainsString('missing_screenshot: '.$this->zip_only_id, $display);
    $this->assertStringContainsString('missing_thumbnail: '.$this->broken_id, $display);
    $this->assertStringContainsString('missing_variants: '.$this->broken_id, $display);
    $this->assertStringContainsString('report_missing_target: '.$this->report_id, $display);
    $this->assertStringContainsString('Dry run', $display);

    $this->assertFileDoesNotExist($this->thumbnail_dir.'screen_'.$this->broken_id.'.png');
    $broken = $this->findProject($this->broken_id);
    $this->assertTrue($broken->getVisible());
    $this->assertFalse($broken->hasMissingFiles());
  }

  public function testHideAndRegenerateFixWhatTheyCan(): void
  {
    $screenshot = $this->screenshot_dir.'screen_'.$this->broken_id.'.png';
    $this->variant_generator->expects($this->once())->method('generate')
      ->with($screenshot, $this->screenshot_dir, 'screen_'.$this->broken_id)
    ;

    $tester = $this->runCommand(['--hide' => true, '--regenerate-thumbnails' => true]);

    $tester->assertCommandIsSuccessful();
    $this->assertFileExists($this->thumbnail_dir.'screen_'.$this->broken_id.'.png');
    $broken = $this->findProject($this->broken_id);
    $this->assertFalse($broken->getVisible());
    $this->assertTrue($broken->hasMissingFiles());
    $this->assertTrue($this->findProject($this->zip_only_id)->getVisible());
    $this->assertTrue($this->findProject($this->extract_only_id)->getVisible());
    $this->assertNotNull($this->entity_manager->find(ContentReport::class, $this->report_id), 'reports are never deleted');
  }

  /**
   * @param array<string, bool> $options
   */
  private function runCommand(array $options): CommandTester
  {
    $tester = new CommandTester(new CheckDataConsistencyCommand(
      $this->entity_manager,
      $this->service(ProjectFileRepository::class),
      $this->service(ScreenshotRepository::class),
      $this->variant_generator,
      $this->extract_dir,
      $this->thumbnail_dir,
    ));
    $tester->execute($options, ['verbosity' => OutputInterface::VERBOSITY_VERBOSE]);

    return $tester;
  }

  private function findProject(string $id): Project
  {
    $this->entity_manager->clear();
    $project = $this->entity_manager->find(Project::class, $id);
    $this->assertInstanceOf(Project::class, $project);

    return $project;
  }

  /**
   * @template T of object
   *
   * @param class-string<T> $id
   *
   * @return T
   */
  private function service(string $id): object
  {
    $service = self::getContainer()->get($id);
    $this->assertInstanceOf($id, $service);

    return $service;
  }

  private function parameter(string $name): string
  {
    $value = self::getContainer()->getParameter($name);
    $this->assertIsString($value);

    return $value;
  }
}
