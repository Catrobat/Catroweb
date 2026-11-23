<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\DB\Entity\Docs\DocPage;
use App\DB\EntityRepository\Docs\DocPageRepository;
use App\Docs\DocsSync;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Process\Process;

/**
 * The sync against the real database, from the fixture copy of the docs repo (no network).
 *
 * @internal
 */
#[CoversClass(DocsSync::class)]
final class DocsSyncTest extends KernelTestCase
{
  private const string FIXTURES = __DIR__.'/../../TestData/Docs';

  private DocsSync $sync;

  private DocPageRepository $pages;

  private EntityManagerInterface $entity_manager;

  #[\Override]
  protected function setUp(): void
  {
    self::bootKernel();
    $sync = self::getContainer()->get(DocsSync::class);
    $this->assertInstanceOf(DocsSync::class, $sync);
    $this->sync = $sync;
    $pages = self::getContainer()->get(DocPageRepository::class);
    $this->assertInstanceOf(DocPageRepository::class, $pages);
    $this->pages = $pages;
    $entity_manager = self::getContainer()->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $entity_manager);
    $this->entity_manager = $entity_manager;
    $this->entity_manager->createQuery('DELETE FROM '.DocPage::class.' p')->execute();
  }

  public function testImportsUpdatesAndDeletes(): void
  {
    $files = DocsSync::readDirectory(self::FIXTURES)['pages'];
    // Imported from WordPress: written by a person, no source hash.
    $files['de/reference/bricks/SetXBrick.md'] = "---\nreviewed: true\n---\n\n\"Setze X auf\" setzt das Objekt an eine Stelle.\n";

    $result = $this->sync->import($files);

    $this->assertSame(['created' => 8, 'updated' => 0, 'deleted' => 0, 'skipped' => []], $result);
    $jump = $this->page('tutorials/jump', 'en');
    $this->assertSame('Make your cat jump', $jump->getTitle());
    $this->assertSame(['embroidery'], $jump->getFlavors());
    $this->assertSame(30, $jump->getSortOrder());
    $this->assertSame(DocPage::STATE_SOURCE, $jump->getTranslationState());
    $this->assertSame(DocPage::STATE_CURRENT, $this->page('index', 'de')->getTranslationState());
    $stale = $this->page('tutorials/jump', 'de');
    $this->assertSame(DocPage::STATE_STALE, $stale->getTranslationState());
    $this->assertTrue($stale->isReviewed());
    $brick = $this->pages->findOneBy(['kind' => DocPage::BRICK, 'path' => 'SetXBrick', 'locale' => 'en']);
    $this->assertNotNull($brick);
    $this->assertStringContainsString('Set x to', $brick->getHtml());
    $german_brick = $this->pages->findOneBy(['kind' => DocPage::BRICK, 'path' => 'SetXBrick', 'locale' => 'de']);
    $this->assertSame(DocPage::STATE_CURRENT, $german_brick?->getTranslationState());
    $this->assertSame('https://github.com/Catrobat/docs/edit/main/en/tutorials/jump.md', $this->sync->editUrl($stale));

    // Nothing changed: nothing is rewritten except the page embedding a project.
    $this->assertSame(['created' => 0, 'updated' => 1, 'deleted' => 0, 'skipped' => []], $this->sync->import($files));

    // The English index changes (the German translation goes stale), a page goes, a broken page is skipped.
    $files['en/index.md'] .= "\nMore.\n";
    unset($files['en/getting-started/index.md']);
    $files['en/broken.md'] = "---\ndescription: no title\n---\nText\n";
    $result = $this->sync->import($files);
    $this->assertSame(['created' => 0, 'updated' => 2, 'deleted' => 1, 'skipped' => ['en/broken.md: front matter has no title']], $result);
    $this->entity_manager->clear();
    $this->assertSame(DocPage::STATE_STALE, $this->page('index', 'de')->getTranslationState());
    $this->assertNull($this->pages->findOneBy(['path' => 'getting-started']));
  }

  public function testRefusesAnImportWithoutStartPage(): void
  {
    $this->sync->import(DocsSync::readDirectory(self::FIXTURES)['pages']);

    try {
      $this->sync->import(['README.md' => 'empty download']);
      $this->fail('expected the import to be refused');
    } catch (\RuntimeException) {
      $this->assertSame(7, $this->pages->count([]));
    }
  }

  public function testReadsAGitHubTarball(): void
  {
    $dir = sys_get_temp_dir().'/docs_tarball_test_'.uniqid();
    $filesystem = new Filesystem();
    $top = $dir.'/src/Catrobat-docs-0123abc';
    $filesystem->mirror(self::FIXTURES, $top);
    // Longer than ustar's 100 characters: PharData would drop it.
    $long = 'tutorials/'.str_repeat('Store_column_1_of_the_comma-separated_values_', 3).'.png';
    $filesystem->copy(self::FIXTURES.'/media/tutorials/jump.png', $top.'/media/'.$long);
    $filesystem->symlink('/etc/hosts', $top.'/media/tutorials/hosts.png');
    try {
      (new Process(['tar', '-czf', $dir.'/docs.tar.gz', '-C', $dir.'/src', 'Catrobat-docs-0123abc']))->mustRun();
      $filesystem->mkdir($dir.'/out');

      $files = DocsSync::readDirectory(DocsSync::extractTarball($dir.'/docs.tar.gz', $dir.'/out'));

      $directory = DocsSync::readDirectory(self::FIXTURES);
      $this->assertEqualsCanonicalizing(array_keys($directory['pages']), array_keys($files['pages']));
      $this->assertSame(file_get_contents(self::FIXTURES.'/en/index.md'), $files['pages']['en/index.md']);
      $this->assertEqualsCanonicalizing(['tutorials/jump.png', 'tutorials/star.svg', $long], array_keys($files['media']), 'the symlink is skipped');
      $this->assertFileEquals(self::FIXTURES.'/media/tutorials/jump.png', $files['media'][$long]);
    } finally {
      $filesystem->remove($dir);
    }
  }

  public function testOnlyServesMediaTypesFromTheMediaFolder(): void
  {
    $this->assertSame('tutorials/jump.png', DocsSync::mediaPath('media/tutorials/jump.png'));
    $this->assertSame('sheet.PDF', DocsSync::mediaPath('media/sheet.PDF'));
    $this->assertNull(DocsSync::mediaPath('media/tutorials/not-served.html'));
    $this->assertNull(DocsSync::mediaPath('media/../en/index.png'));
    $this->assertNull(DocsSync::mediaPath('media/.hidden.png'));
    $this->assertNull(DocsSync::mediaPath('en/jump.png'));
    $this->assertNull(DocsSync::mediaPath('media/a/..png/../b.png'));
  }

  public function testCopiesTheMediaAndReplacesTheOldCopy(): void
  {
    $this->assertSame(2, $this->sync->importMedia(DocsSync::readDirectory(self::FIXTURES)['media']));
    $file = $this->sync->mediaFile('tutorials/jump.png');
    $this->assertNotNull($file);
    $this->assertFileEquals(self::FIXTURES.'/media/tutorials/jump.png', $file);
    $this->assertNotNull($this->sync->mediaFile('tutorials/star.svg'));
    $this->assertNull($this->sync->mediaFile('tutorials/not-served.html'));
    $this->assertNull($this->sync->mediaFile('../../.env'));

    $kernel = self::$kernel;
    $this->assertNotNull($kernel);
    $svg = $kernel->handle(Request::create('/app/docs/media/tutorials/star.svg'), HttpKernelInterface::MAIN_REQUEST, false);
    $this->assertSame(200, $svg->getStatusCode());
    $this->assertSame('image/svg+xml', $svg->headers->get('Content-Type'));
    $this->assertStringStartsWith('sandbox;', (string) $svg->headers->get('Content-Security-Policy'), 'an SVG opened directly runs no script');
    try {
      $kernel->handle(Request::create('/app/docs/media/tutorials/not-served.html'), HttpKernelInterface::MAIN_REQUEST, false);
      $this->fail('only media types are served');
    } catch (NotFoundHttpException) {
    }

    $this->sync->importMedia(['tutorials/star.svg' => self::FIXTURES.'/media/tutorials/star.svg']);
    $this->assertNull($this->sync->mediaFile('tutorials/jump.png'), 'a file gone from the repo is gone from share');

    $this->expectException(\InvalidArgumentException::class);
    $this->sync->importMedia(['../escape.png' => self::FIXTURES.'/media/tutorials/jump.png']);
  }

  private function page(string $path, string $locale): DocPage
  {
    $page = $this->pages->findOneBy(['kind' => DocPage::GUIDE, 'path' => $path, 'locale' => $locale]);
    $this->assertNotNull($page);

    return $page;
  }
}
