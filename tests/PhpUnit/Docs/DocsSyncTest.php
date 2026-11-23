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
    $files = DocsSync::readDirectory(self::FIXTURES);
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
    $this->sync->import(DocsSync::readDirectory(self::FIXTURES));

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
    $filesystem->mirror(self::FIXTURES, $dir.'/src/Catrobat-docs-0123abc');
    $filesystem->dumpFile($dir.'/src/Catrobat-docs-0123abc/media/tutorials/jump.png', 'png');
    try {
      $tar = new \PharData($dir.'/docs.tar');
      $tar->buildFromDirectory($dir.'/src');
      $tar->compress(\Phar::GZ);

      $files = DocsSync::readTarball($dir.'/docs.tar.gz');

      $this->assertEqualsCanonicalizing(array_keys(DocsSync::readDirectory(self::FIXTURES)), array_keys($files));
      $this->assertSame(file_get_contents(self::FIXTURES.'/en/index.md'), $files['en/index.md']);
    } finally {
      $filesystem->remove($dir);
    }
  }

  private function page(string $path, string $locale): DocPage
  {
    $page = $this->pages->findOneBy(['kind' => DocPage::GUIDE, 'path' => $path, 'locale' => $locale]);
    $this->assertNotNull($page);

    return $page;
  }
}
