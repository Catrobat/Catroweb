<?php

declare(strict_types=1);

namespace App\Studio;

use Elastica\Query\BoolQuery;
use Elastica\Query\QueryString;
use Elastica\Query\Terms;
use Elastica\Util;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

class StudioSearchService
{
  public function __construct(
    #[Autowire(service: 'fos_elastica.finder.app_studio')]
    private readonly TransformedFinder $studio_finder,
  ) {
  }

  public function search(string $query, int $limit = 20, int $offset = 0): array
  {
    $studio_query = $this->studioSearchQuery($query);

    return $this->studio_finder->find($studio_query, $limit, ['from' => $offset]);
  }

  public function searchCount(string $query): int
  {
    $studio_query = $this->studioSearchQuery($query);

    $paginator = $this->studio_finder->findPaginated($studio_query);

    return $paginator->getNbResults();
  }

  private function studioSearchQuery(string $query): BoolQuery
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
    $query_string->setFields(['name^3', 'description']);
    $query_string->setAnalyzeWildcard();
    $query_string->setDefaultOperator('AND');

    $bool_query = new BoolQuery();

    $bool_query->addMust(new Terms('is_enabled', [true]));
    $bool_query->addMust(new Terms('auto_hidden', [false]));

    $bool_query->addMust($query_string);

    return $bool_query;
  }
}
