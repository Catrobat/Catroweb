<?php

declare(strict_types=1);

namespace Tests\PhpUnit\Docs;

use App\DB\Entity\Docs\DocPage;
use App\Docs\DocsSync;
use App\Docs\Search\DocsEmbedder;
use App\Docs\Search\DocsSearchIndexer;
use App\System\Testing\FakeDocsEmbedder;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

/**
 * The index against the real database and Elasticsearch, with the test environment's FakeDocsEmbedder.
 *
 * @internal
 */
#[CoversClass(DocsSearchIndexer::class)]
final class DocsSearchIndexerTest extends KernelTestCase
{
  public function testOnlyNewAndChangedEntriesAreEmbedded(): void
  {
    self::bootKernel();
    $container = self::getContainer();
    $entity_manager = $container->get(EntityManagerInterface::class);
    $this->assertInstanceOf(EntityManagerInterface::class, $entity_manager);
    $entity_manager->createQuery('DELETE FROM '.DocPage::class.' p')->execute();
    $sync = $container->get(DocsSync::class);
    $indexer = $container->get(DocsSearchIndexer::class);
    $embedder = $container->get(DocsEmbedder::class);
    $this->assertInstanceOf(DocsSync::class, $sync);
    $this->assertInstanceOf(DocsSearchIndexer::class, $indexer);
    $this->assertInstanceOf(FakeDocsEmbedder::class, $embedder);
    $files = DocsSync::readDirectory($sync->docs_dir);
    $sync->import($files);

    $count = $indexer->rebuild();
    $embedded = $embedder->embedded;
    $this->assertSame($count, $indexer->rebuild());
    $this->assertSame($embedded, $embedder->embedded, 'nothing changed, nothing is embedded again');

    $files['en/tutorials/jump.md'] .= "\nJump twice.\n";
    $sync->import($files);
    $indexer->rebuild();
    $this->assertSame($embedded + 1, $embedder->embedded, 'only the changed page');
  }
}
