<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\Application\Controller\Docs\DocsController;
use App\DB\Entity\Docs\DocPage;
use App\DB\EntityRepository\Docs\DocPageRepository;
use App\Docs\DocsSync;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * The sync against the real database, from the fixture docs folder (the test environment's catrobat.user_docs.dir).
 *
 * @internal
 */
#[CoversClass(DocsSync::class)]
#[CoversClass(DocsController::class)]
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

    $this->assertSame(['created' => 9, 'updated' => 0, 'deleted' => 0, 'skipped' => []], $result);
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

  public function testAReplacedImageReRendersThePagesNamingIt(): void
  {
    $files = DocsSync::readDirectory(self::FIXTURES);
    $files['en/picture.md'] = "---\ntitle: Picture\n---\n![Jump](../media/tutorials/jump.png)\n";
    $this->sync->import($files, ['tutorials/jump.png' => '1111111111']);
    $this->assertStringContainsString('src="/docs-media/tutorials/jump.png?v=1111111111"', $this->page('picture', 'en')->getHtml());

    // Only the page embedding a project and the one showing the replaced image are rendered again.
    $this->assertSame(['created' => 0, 'updated' => 2, 'deleted' => 0, 'skipped' => []], $this->sync->import($files, ['tutorials/jump.png' => '2222222222']));
    $this->entity_manager->clear();
    $this->assertStringContainsString('src="/docs-media/tutorials/jump.png?v=2222222222"', $this->page('picture', 'en')->getHtml());
  }

  public function testRefusesAnImportWithoutStartPage(): void
  {
    $this->sync->import(DocsSync::readDirectory(self::FIXTURES));

    try {
      $this->sync->import(['README.md' => 'empty download']);
      $this->fail('expected the import to be refused');
    } catch (\RuntimeException) {
      $this->assertSame(9, $this->pages->count([]));
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

  public function testServesThePagesAsMarkdownAndLlmsTxt(): void
  {
    $this->sync->import(DocsSync::readDirectory(self::FIXTURES));
    $kernel = self::$kernel;
    $this->assertNotNull($kernel);
    $get = static fn (string $url): Response => $kernel->handle(Request::create($url), HttpKernelInterface::MAIN_REQUEST, false);

    $page = $get('/pocketcode/docs/getting-started/first-project.md');
    $this->assertSame(200, $page->getStatusCode());
    $this->assertSame('text/markdown; charset=utf-8', $page->headers->get('Content-Type'));
    $this->assertSame('nosniff', $page->headers->get('X-Content-Type-Options'));
    $this->assertStringStartsWith("# Your first project\n\n> Make an object move.\n\n## Move the cat", (string) $page->getContent());
    $this->assertStringContainsString('(http://localhost/app/docs/tutorials/jump#jump-higher)', (string) $page->getContent(), 'links lead to /app/');

    $german = $get('/app/docs/tutorials/jump.md?locale=de_DE');
    $this->assertStringStartsWith('# Lass deine Katze springen', (string) $german->getContent());
    $this->assertTrue($german->headers->getCacheControlDirective('public'));
    $this->assertSame(200, $get('/app/docs/index.md')->getStatusCode());

    $brick = $get('/app/docs/reference/bricks/SetXBrick.md');
    $this->assertSame(200, $brick->getStatusCode());
    $this->assertStringStartsWith("# Set x to 100\n\nCatrobat brick `SetXBrick`, in ", (string) $brick->getContent());
    $this->assertStringContainsString('puts the object at a position', (string) $brick->getContent());
    $this->assertStringNotContainsString('To see this brick', (string) $brick->getContent(), 'always shown');
    $this->assertStringContainsString("\n> To see this brick in the app, turn on “Lego Mindstorms NXT extension” in the settings.\n", (string) $get('/app/docs/reference/bricks/LegoNxtMotorTurnAngleBrick.md')->getContent());
    foreach (['/app/docs/does-not-exist.md', '/app/docs/reference/bricks/NoSuchBrick.md'] as $missing) {
      try {
        $get($missing);
        $this->fail($missing.' should not be found');
      } catch (NotFoundHttpException $notFoundHttpException) {
        $this->assertSame(404, $notFoundHttpException->getStatusCode());
      }
    }

    $html = (string) $get('/app/docs/tutorials/jump')->getContent();
    $this->assertStringContainsString('<link rel="alternate" type="text/markdown" href="/app/docs/tutorials/jump.md">', $html);

    $llms = $get('/llms.txt');
    $this->assertSame(200, $llms->getStatusCode());
    $this->assertSame('text/plain; charset=utf-8', $llms->headers->get('Content-Type'));
    $this->assertTrue($llms->headers->getCacheControlDirective('public'));
    $text = (string) $llms->getContent();
    $this->assertStringStartsWith("# Catrobat documentation\n\n> Learn to make games", $text);
    $this->assertStringContainsString('## Getting started', $text);
    $this->assertStringContainsString('- [Your first project](http://localhost/app/docs/getting-started/first-project.md)', $text);
    $this->assertStringContainsString('- [Set x to 100](http://localhost/app/docs/reference/bricks/SetXBrick.md): ', $text);
  }

  public function testReadsTheMedia(): void
  {
    $this->assertSame(['tutorials/jump.png' => '6b7fa434f9'], DocsSync::readMedia(self::FIXTURES));
    $this->assertSame([], DocsSync::readMedia(self::FIXTURES.'/en'));
  }

  public function testRefusesMediaThatMayNotGoLive(): void
  {
    $dir = sys_get_temp_dir().'/docs_media_'.uniqid();
    mkdir($dir.'/media/a', 0o777, true);
    touch($dir.'/media/.gitkeep');
    touch($dir.'/media/a/ok.PNG');
    touch($dir.'/media/a/x.svg');
    touch($dir.'/media/page.html');
    symlink(sys_get_temp_dir(), $dir.'/media/out'); // a linked folder leading out of media/
    symlink($dir.'/media/a/ok.PNG', $dir.'/media/linked.png');
    try {
      DocsSync::readMedia($dir);
      $this->fail('expected the media to be refused');
    } catch (\RuntimeException $e) {
      $this->assertStringEndsWith(': a/x.svg, linked.png, out, page.html', $e->getMessage());
    } finally {
      (new \Symfony\Component\Filesystem\Filesystem())->remove($dir);
    }
  }

  private function page(string $path, string $locale): DocPage
  {
    $page = $this->pages->findOneBy(['kind' => DocPage::GUIDE, 'path' => $path, 'locale' => $locale]);
    $this->assertNotNull($page);

    return $page;
  }
}
