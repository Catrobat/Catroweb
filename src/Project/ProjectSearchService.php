<?php

declare(strict_types=1);

namespace App\Project;

use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;
use Elastica\Query\Range;
use Elastica\Query\Terms;
use Elastica\Util;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class ProjectSearchService
{
  public function __construct(
    #[Autowire(service: 'fos_elastica.finder.app_program')]
    private readonly TransformedFinder $program_finder,
  ) {
  }

  public function search(string $query, ?int $limit = 20, int $offset = 0, string $max_version = '', ?string $flavor = null, bool $is_debug_request = false): array
  {
    $project_query = $this->projectSearchQuery($query, $max_version, $flavor, $is_debug_request);

    return $this->program_finder->find($project_query, $limit, ['from' => $offset]);
  }

  public function searchCount(string $query, string $max_version = '', ?string $flavor = null, bool $is_debug_request = false): int
  {
    $project_query = $this->projectSearchQuery($query, $max_version, $flavor, $is_debug_request);

    $paginator = $this->program_finder->findPaginated($project_query);

    return $paginator->getNbResults();
  }

  private function projectSearchQuery(string $query, string $max_version = '', ?string $flavor = null, bool $is_debug_request = false): BoolQuery
  {
    $query = Util::escapeTerm($query);

    $words = explode(' ', $query);
    foreach ($words as &$word) {
      $word .= '*';
    }

    unset($word);
    $query = implode(' ', $words);

    $query_string = new QueryString();
    $query_string->setQuery($query);
    $query_string->setFields(['id', 'name^3', 'description', 'getUsernameString', 'getTagsString', 'getExtensionsString']);
    $query_string->setAnalyzeWildcard();
    $query_string->setDefaultOperator('AND');

    $bool_query = new BoolQuery();

    $bool_query->addMust(new Terms('private', [false]));
    $bool_query->addMust(new Terms('visible', [true]));

    if (!$is_debug_request) {
      $bool_query->addMust(new Terms('debug_build', [false]));
    }

    if ('' !== $max_version) {
      $bool_query->addMust(new Range('language_version', ['lte' => $max_version]));
    }

    if (null !== $flavor && '' !== trim($flavor)) {
      $bool_query->addMust(new Terms('flavor', [$flavor]));
    }

    $bool_query->addMust($query_string);

    return $bool_query;
  }
}
