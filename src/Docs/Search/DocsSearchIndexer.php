<?php

declare(strict_types=1);

namespace App\Docs\Search;

use App\DB\Entity\Docs\DocEmbedding;
use App\DB\Entity\Docs\DocPage;
use App\DB\EntityRepository\Docs\DocPageRepository;
use App\Docs\DocsCatalog;
use App\Docs\DocsLibrary;
use Doctrine\ORM\EntityManagerInterface;
use Elastica\Document;
use FOS\ElasticaBundle\Index\IndexManager;
use FOS\ElasticaBundle\Index\Resetter;
use FOS\ElasticaBundle\Provider\PagerfantaPager;
use FOS\ElasticaBundle\Provider\PagerInterface;
use FOS\ElasticaBundle\Provider\PagerProviderInterface;
use FOS\ElasticaBundle\Transformer\ModelToElasticaTransformerInterface;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Fills the app_doc index: one document per guide and per brick in every locale, with its text and its vector.
 * Vectors are kept in doc_embedding by a hash of model, prefix and text, so only new and changed entries are sent to
 * the embedding server. Runs after every docs sync (rebuild()) and as the index's provider for fos:elastica:populate,
 * whose transformer it also is: its documents are complete Elastica documents already.
 */
class DocsSearchIndexer implements PagerProviderInterface, ModelToElasticaTransformerInterface
{
  final public const string INDEX = 'app_doc';

  final public const string GUIDE = 'guide';
  final public const string BRICK = 'brick';
  final public const string FORMULA = 'formula';

  /** The embedding server's batch limit (text-embeddings-inference: --max-client-batch-size, 32 by default). */
  private const int BATCH = 32;

  public function __construct(
    private readonly DocPageRepository $pages,
    private readonly DocsEmbedder $embedder,
    private readonly IndexManager $indexes,
    private readonly Resetter $resetter,
    private readonly EntityManagerInterface $entity_manager,
    private readonly DocsCatalog $catalog,
    private readonly DocsLibrary $library,
  ) {
  }

  /** Replaces the index with the docs as they are now. */
  public function rebuild(): int
  {
    $documents = $this->documents();
    $this->resetter->resetIndex(self::INDEX);
    $index = $this->indexes->getIndex(self::INDEX);
    if ([] !== $documents) {
      $index->addDocuments($documents);
    }
    $index->refresh();

    return \count($documents);
  }

  #[\Override]
  public function provide(array $options = []): PagerInterface
  {
    return new PagerfantaPager(new Pagerfanta(new ArrayAdapter($this->documents())));
  }

  #[\Override]
  public function transform(object $object, array $fields): Document
  {
    \assert($object instanceof Document);

    return $object;
  }

  /**
   * @return list<Document>
   */
  private function documents(): array
  {
    $entries = [...$this->guideEntries(), ...$this->brickEntries()];
    $keys = array_map(fn (array $entry): string => $this->embedder->passageKey(self::passage($entry)), $entries);
    $vectors = $this->storedVectors($keys);

    $missing = array_keys(array_diff_key(array_flip($keys), $vectors));
    $passages = [];
    foreach ($entries as $i => $entry) {
      $passages[$keys[$i]] = self::passage($entry);
    }
    foreach (array_chunk($missing, self::BATCH) as $batch) {
      $embedded = $this->embedder->embedPassages(array_map(static fn (string $key): string => $passages[$key], $batch));
      if (null === $embedded) {
        // Not configured or failed: the rest goes without vectors (found by keywords), so a down server does not
        // hold the deploy for a timeout per batch.
        break;
      }
      $stored = [];
      foreach ($batch as $j => $key) {
        $vectors[$key] = $embedded[$j];
        $this->entity_manager->persist($stored[] = new DocEmbedding($key, $embedded[$j]));
      }
      $this->entity_manager->flush();
      array_map($this->entity_manager->detach(...), $stored);
    }
    // Vectors of entries that are gone. ponytail: one NOT IN with every key; chunk it past ~50k entries.
    $this->entity_manager->createQuery('DELETE FROM '.DocEmbedding::class.' e WHERE e.text_hash NOT IN (:keys)')
      ->setParameter('keys', [] === $keys ? [''] : $keys)->execute()
    ;

    $documents = [];
    foreach ($entries as $i => $entry) {
      $data = $entry + ['snippet' => self::snippet($entry['description'], $entry['text'])];
      unset($data['description']);
      if (isset($vectors[$keys[$i]])) {
        $data['embedding'] = $vectors[$keys[$i]];
      }
      $documents[] = new Document($entry['type'].':'.$entry['locale'].':'.$entry['path'], $data);
    }

    return $documents;
  }

  /**
   * @return list<array{type: string, path: string, locale: string, title: string, description: string|null, text: string}>
   */
  private function guideEntries(): array
  {
    return array_map(static fn (DocPage $page): array => [
      'type' => self::GUIDE,
      'path' => $page->getPath(),
      'locale' => $page->getLocale(),
      'title' => $page->getTitle(),
      'description' => $page->getDescription(),
      'text' => self::text($page->getHtml()),
    ], $this->pages->findBy(['kind' => DocPage::GUIDE]));
  }

  /**
   * Every brick and formula element of the reference (DocsCatalog) in every language the docs have, titled by its
   * localized name, with the brick's explanation (`reference/bricks/<type>.md`, best language first) as text. A brick
   * with an explanation but not in the catalog (no player build deployed) is still found, titled by the file.
   *
   * @return list<array{type: string, path: string, locale: string, title: string, description: string|null, text: string}>
   */
  private function brickEntries(): array
  {
    $prose = [];
    foreach ($this->pages->findBy(['kind' => DocPage::BRICK]) as $page) {
      $prose[$page->getPath()][$page->getLocale()] = $page;
    }
    /** @var list<array{locale: string}> $rows */
    $rows = $this->pages->createQueryBuilder('p')->select('DISTINCT p.locale')->getQuery()->getArrayResult();

    $entries = [];
    foreach (array_column($rows, 'locale') as $locale) {
      $locales = $this->library->locales($locale);
      $explanation = static function (string $type) use ($prose, $locales): ?DocPage {
        foreach ($locales as $candidate) {
          if (isset($prose[$type][$candidate])) {
            return $prose[$type][$candidate];
          }
        }

        return null;
      };
      $catalog = $this->catalog->catalog($locale);
      $bricks = [];
      foreach ($catalog['categories'] as $category) {
        foreach ($category['bricks'] as $brick) {
          $bricks[(string) $brick['type']] ??= $this->catalog->title($brick);
        }
      }
      foreach (array_keys($prose) as $type) {
        $bricks[(string) $type] ??= $explanation((string) $type)?->getTitle() ?: (string) $type;
      }
      foreach ($bricks as $type => $title) {
        $page = $explanation((string) $type);
        $entries[] = [
          'type' => self::BRICK,
          'path' => 'reference/bricks/'.$type,
          'locale' => $locale,
          'title' => $title,
          'description' => $page?->getDescription(),
          'text' => null === $page ? '' : self::text($page->getHtml()),
        ];
      }
      foreach ($catalog['formula'] as $elements) {
        foreach ($elements as $element) {
          $entries[] = [
            'type' => self::FORMULA,
            'path' => 'reference/formula/'.$element['name'],
            'locale' => $locale,
            'title' => $element['label'] ?? $element['name'],
            'description' => null,
            'text' => $element['name'],
          ];
        }
      }
    }

    return $entries;
  }

  /**
   * The text that is embedded for an entry.
   *
   * @param array{title: string, text: string, ...} $entry
   */
  private static function passage(array $entry): string
  {
    return $entry['title']."\n".$entry['text'];
  }

  /**
   * @param list<string> $keys
   *
   * @return array<string, list<float>> key => vector
   */
  private function storedVectors(array $keys): array
  {
    $vectors = [];
    foreach (array_chunk(array_values(array_unique($keys)), 1000) as $chunk) {
      /** @var list<DocEmbedding> $stored */
      $stored = $this->entity_manager->getRepository(DocEmbedding::class)->findBy(['text_hash' => $chunk]);
      foreach ($stored as $embedding) {
        $vectors[$embedding->getTextHash()] = $embedding->getVector();
        $this->entity_manager->detach($embedding);
      }
    }

    return $vectors;
  }

  private static function text(string $html): string
  {
    $text = html_entity_decode(strip_tags(str_replace('<', ' <', $html)), ENT_QUOTES | ENT_HTML5);

    return trim((string) preg_replace('/\s+/u', ' ', $text));
  }

  private static function snippet(?string $description, string $text): string
  {
    $snippet = '' === (string) $description ? $text : (string) $description;

    return mb_strlen($snippet) > 200 ? rtrim(mb_substr($snippet, 0, 199)).'…' : $snippet;
  }
}
