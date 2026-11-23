<?php

declare(strict_types=1);

namespace App\Docs\Search;

use App\Docs\DocsLibrary;
use Elastica\Exception\ExceptionInterface;
use FOS\ElasticaBundle\Index\IndexManager;
use Psr\Log\LoggerInterface;

/**
 * Hybrid search over the docs: keywords (BM25) and meaning (kNN over e5 vectors, so a question in any language finds
 * pages in any other), merged by reciprocal rank fusion. Elasticsearch refuses its RRF retriever on the free (basic)
 * licence, so the fusion happens here. Without vectors (no embedding server, or it failed) it is a keyword search.
 */
class DocsSearch
{
  final public const int MAX_QUERY_LENGTH = 200;

  /** Hits taken from each of the two searches before fusion. */
  private const int CANDIDATES = 50;

  /** RRF's constant: a document's score is the sum of 1 / (K + rank) over the rankings it is in. */
  private const int RRF_K = 60;

  /**
   * kNN always returns its k nearest documents, however far. multilingual-e5-small puts every text at a cosine
   * similarity of 0.75 to 0.9 to any query, even with its "query: "/"passage: " prefixes (the model card: low training
   * temperature), so a hit must reach MIN_SIMILARITY and be within SIMILARITY_MARGIN of the best hit. Measured on the
   * test docs and catalog: matching entries 0.83-0.90, unrelated ones up to 0.82. ponytail: fixed values; tune them
   * with `bin/console catrobat:docs:search` on the real docs, or when the model changes.
   */
  private const float MIN_SIMILARITY = 0.83;

  private const float SIMILARITY_MARGIN = 0.05;

  public function __construct(
    private readonly IndexManager $indexes,
    private readonly DocsEmbedder $embedder,
    private readonly DocsLibrary $library,
    private readonly LoggerInterface $docsSearchLogger,
    private readonly LoggerInterface $logger,
  ) {
  }

  /**
   * Results by type (guide, brick, formula), best first, at most $limit each; one result per page, in the best of
   * the visitor's languages that matched.
   *
   * @return array{groups: array<string, list<array{type: string, path: string, locale: string, title: string, snippet: string}>>, semantic: bool}
   */
  public function search(string $query, string $locale, int $limit = 10): array
  {
    $query = trim($query);
    $locales = $this->library->locales($locale);
    $filter = ['terms' => ['locale' => $locales]];

    $keyword = $this->hits(['bool' => [
      // Most of the words must match: without stopwords, "how do I play a sound" otherwise finds every page with "a".
      'must' => ['multi_match' => ['query' => $query, 'fields' => ['title^3', 'text'], 'minimum_should_match' => '2<75%']],
      'filter' => $filter,
    ]]);
    $rankings = [array_keys($keyword)];
    $hits = $keyword;

    $vector = $this->embedder->embedQuery($query);
    if (null !== $vector) {
      // A knn query, not the top-level knn section: Elastica adds match_all to a search without a query.
      $semantic = $this->hits(['knn' => [
        'field' => 'embedding',
        'query_vector' => $vector,
        'k' => self::CANDIDATES,
        'num_candidates' => 2 * self::CANDIDATES,
        'similarity' => self::MIN_SIMILARITY,
        'filter' => $filter,
      ]], self::SIMILARITY_MARGIN / 2); // the kNN score of a cosine similarity is (1 + similarity) / 2
      $rankings[] = array_keys($semantic);
      $hits += $semantic;
    }

    $groups = [DocsSearchIndexer::GUIDE => [], DocsSearchIndexer::BRICK => [], DocsSearchIndexer::FORMULA => []];
    $seen = [];
    foreach (array_keys(self::fuse($rankings)) as $id) {
      $hit = $hits[$id];
      $page = $hit['type'].':'.$hit['path'];
      if (isset($seen[$page]) || !isset($groups[$hit['type']]) || \count($groups[$hit['type']]) >= $limit) {
        continue;
      }
      $seen[$page] = true;
      $groups[$hit['type']][] = self::bestLocale($hit, $hits, $locales);
    }

    if ([] === array_merge(...array_values($groups))) {
      // Content gaps: what people look for and do not find. Query and language only, never who asked.
      $this->docsSearchLogger->info('docs_search_no_results', ['query' => mb_substr($query, 0, self::MAX_QUERY_LENGTH), 'locale' => $locale]);
    }

    return ['groups' => $groups, 'semantic' => null !== $vector];
  }

  /**
   * Reciprocal rank fusion: document id => score, best first. A document high in both rankings beats one that is
   * first in only one of them.
   *
   * @param list<list<string>> $rankings document ids, best first
   *
   * @return array<string, float>
   */
  public static function fuse(array $rankings): array
  {
    $scores = [];
    foreach ($rankings as $ranking) {
      foreach ($ranking as $rank => $id) {
        $scores[$id] = ($scores[$id] ?? 0.0) + 1.0 / (self::RRF_K + $rank + 1);
      }
    }
    arsort($scores);

    return $scores;
  }

  /**
   * The same page may match in several of the visitor's languages (de_DE and the English original): show the best.
   *
   * @param array{type: string, path: string, locale: string, title: string, snippet: string}                $hit
   * @param array<string, array{type: string, path: string, locale: string, title: string, snippet: string}> $hits
   * @param list<string>                                                                                     $locales
   *
   * @return array{type: string, path: string, locale: string, title: string, snippet: string}
   */
  private static function bestLocale(array $hit, array $hits, array $locales): array
  {
    foreach ($locales as $locale) {
      $match = $hits[$hit['type'].':'.$locale.':'.$hit['path']] ?? null;
      if (null !== $match) {
        return $match;
      }
    }

    return $hit;
  }

  /**
   * @param array<string, array<string, mixed>> $query  the query clause
   * @param float|null                          $margin only hits scoring at most this much below the best
   *
   * @return array<string, array{type: string, path: string, locale: string, title: string, snippet: string}> by id
   */
  private function hits(array $query, ?float $margin = null): array
  {
    try {
      $results = $this->indexes->getIndex(DocsSearchIndexer::INDEX)->search([
        'query' => $query,
        '_source' => ['type', 'path', 'locale', 'title', 'snippet'],
        'size' => self::CANDIDATES,
      ])->getResults();
    } catch (ExceptionInterface $e) {
      // No index yet (before the first sync) or Elasticsearch is down: no results, not an error page.
      $this->logger->error('Docs search failed: '.$e->getMessage());

      return [];
    }
    $hits = [];
    $best = null === $margin || [] === $results ? null : (float) $results[0]->getScore();
    foreach ($results as $result) {
      if (null !== $best && (float) $result->getScore() < $best - (float) $margin) {
        break;
      }
      /** @var array{type: string, path: string, locale: string, title: string, snippet: string} $source */
      $source = $result->getSource();
      $hits[(string) $result->getId()] = $source;
    }

    return $hits;
  }
}
