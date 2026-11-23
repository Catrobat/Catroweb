<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\DB\Entity\Docs\DocPage;
use App\DB\EntityRepository\Docs\DocPageRepository;
use App\Docs\DocsSync;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * The sync against the real database, from the fixture docs folder (the test environment's catrobat.user_docs.dir).
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
    // de_DE/reference/bricks/SetXBrick.md was imported from WordPress: written by a person, no source hash.

    $result = $this->sync->import($files);

    $this->assertSame(['created' => 8, 'updated' => 0, 'deleted' => 0, 'skipped' => []], $result);
    $jump = $this->page('tutorials/jump', 'en');
    $this->assertSame('Make your cat jump', $jump->getTitle());
    $this->assertSame(['embroidery'], $jump->getFlavors());
    $this->assertSame(30, $jump->getSortOrder());
    $this->assertSame(DocPage::STATE_SOURCE, $jump->getTranslationState());
    $this->assertSame(DocPage::STATE_CURRENT, $this->page('index', 'de_DE')->getTranslationState());
    $stale = $this->page('tutorials/jump', 'de_DE');
    $this->assertSame(DocPage::STATE_STALE, $stale->getTranslationState());
    $this->assertTrue($stale->isReviewed());
    $brick = $this->pages->findOneBy(['kind' => DocPage::BRICK, 'path' => 'SetXBrick', 'locale' => 'en']);
    $this->assertNotNull($brick);
    $this->assertStringContainsString('Set x to', $brick->getHtml());
    $german_brick = $this->pages->findOneBy(['kind' => DocPage::BRICK, 'path' => 'SetXBrick', 'locale' => 'de_DE']);
    $this->assertSame(DocPage::STATE_CURRENT, $german_brick?->getTranslationState());
    $this->assertSame('https://github.com/Catrobat/Catroweb/edit/main/user-docs/en/tutorials/jump.md', $this->sync->editUrl($stale));

    // Nothing changed: nothing is rewritten except the page embedding a project.
    $this->assertSame(['created' => 0, 'updated' => 1, 'deleted' => 0, 'skipped' => []], $this->sync->import($files));

    // The English index changes (the German translation goes stale), a page goes, a broken page is skipped.
    $files['en/index.md'] .= "\nMore.\n";
    unset($files['en/getting-started/index.md']);
    $files['en/broken.md'] = "---\ndescription: no title\n---\nText\n";
    $result = $this->sync->import($files);
    $this->assertSame(['created' => 0, 'updated' => 2, 'deleted' => 1, 'skipped' => ['en/broken.md: front matter has no title']], $result);
    $this->entity_manager->clear();
    $this->assertSame(DocPage::STATE_STALE, $this->page('index', 'de_DE')->getTranslationState());
    $this->assertNull($this->pages->findOneBy(['path' => 'getting-started']));
  }

  public function testRefusesAnImportWithoutStartPage(): void
  {
    $this->sync->import(DocsSync::readDirectory(self::FIXTURES));

    try {
      $this->sync->import(['README.md' => 'empty download']);
      $this->fail('expected the import to be refused');
    } catch (\RuntimeException) {
      $this->assertSame(8, $this->pages->count([]));
    }
  }

  public function testOnlyAnExplicitLocaleMayBeCachedByACdn(): void
  {
    $this->sync->import(DocsSync::readDirectory(self::FIXTURES));
    $kernel = self::$kernel;
    $this->assertNotNull($kernel);
    foreach (['/api/docs', '/api/docs/pages/index'] as $url) {
      $by_language = $kernel->handle(Request::create($url, server: ['HTTP_ACCEPT_LANGUAGE' => 'de']), HttpKernelInterface::MAIN_REQUEST, false);
      $this->assertSame(200, $by_language->getStatusCode());
      $this->assertTrue($by_language->headers->getCacheControlDirective('private'), $url.' depends on Accept-Language');
      $explicit = $kernel->handle(Request::create($url.'?locale=de'), HttpKernelInterface::MAIN_REQUEST, false);
      $this->assertTrue($explicit->headers->getCacheControlDirective('public'), $url.'?locale=de');
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

  public function testServesMediaStraightFromTheDocsFolder(): void
  {
    $this->assertSame(realpath(self::FIXTURES), realpath($this->sync->docs_dir));
    $file = $this->sync->mediaFile('tutorials/jump.png');
    $this->assertNotNull($file);
    $this->assertFileEquals(self::FIXTURES.'/media/tutorials/jump.png', $file);
    $this->assertNull($this->sync->mediaFile('tutorials/not-served.html'));
    $this->assertNull($this->sync->mediaFile('../en/index.md'));
    $this->assertNull($this->sync->mediaFile('../../../.env'));
    $outside = sys_get_temp_dir().'/docs_outside_'.uniqid();
    mkdir($outside);
    copy(self::FIXTURES.'/media/tutorials/jump.png', $outside.'/secret.png');
    $link = self::FIXTURES.'/media/outside-link';
    symlink($outside, $link); // a linked folder leading out of media/
    try {
      $this->assertNull($this->sync->mediaFile('outside-link/secret.png'));
    } finally {
      unlink($link);
      unlink($outside.'/secret.png');
      rmdir($outside);
    }

    $kernel = self::$kernel;
    $this->assertNotNull($kernel);
    $svg = $kernel->handle(Request::create('/app/docs/_media/tutorials/star.svg'), HttpKernelInterface::MAIN_REQUEST, false);
    $this->assertSame(200, $svg->getStatusCode());
    $this->assertSame('image/svg+xml', $svg->headers->get('Content-Type'));
    $this->assertStringStartsWith('sandbox;', (string) $svg->headers->get('Content-Security-Policy'), 'an SVG opened directly runs no script');
    try {
      $kernel->handle(Request::create('/app/docs/_media/tutorials/not-served.html'), HttpKernelInterface::MAIN_REQUEST, false);
      $this->fail('only media types are served');
    } catch (NotFoundHttpException) {
    }
  }

  private function page(string $path, string $locale): DocPage
  {
    $page = $this->pages->findOneBy(['kind' => DocPage::GUIDE, 'path' => $path, 'locale' => $locale]);
    $this->assertNotNull($page);

    return $page;
  }
}
