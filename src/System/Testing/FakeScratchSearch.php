<?php

declare(strict_types=1);

namespace App\System\Testing;

use App\DB\Entity\Project\Scratch\ScratchProject;
use App\DB\EntityRepository\Project\ProjectRepository;
use App\DB\EntityRepository\Project\ScratchProjectRepository;
use App\Project\Scratch\ScratchSearch;
use Psr\Log\LoggerInterface;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Stands in for Scratch's search API in tests: "Scratch" knows exactly the projects seeded with
 * "Given there are Scratch projects:", matched by name or username like Scratch does.
 */
class FakeScratchSearch extends ScratchSearch
{
  public function __construct(
    HttpClientInterface $http_client,
    CacheInterface $cache,
    RateLimiterFactoryInterface $scratchSearchBurstLimiter,
    ProjectRepository $project_repository,
    LoggerInterface $logger,
    private readonly ScratchProjectRepository $scratch_project_repository,
  ) {
    parent::__construct($http_client, $cache, $scratchSearchBurstLimiter, $project_repository, $logger);
  }

  #[\Override]
  protected function fetch(string $query, int $limit, int $offset): array
  {
    /** @var ScratchProject[] $projects */
    $projects = $this->scratch_project_repository->createQueryBuilder('s')
      ->where('s.name LIKE :query OR s.username LIKE :query')
      ->setParameter('query', '%'.addcslashes($query, '%_\\').'%')
      ->orderBy('s.id', 'ASC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
      ->getQuery()
      ->getResult()
    ;

    return array_values(array_map(static fn (ScratchProject $project): array => [
      'id' => $project->getId(),
      'name' => (string) $project->getName(),
      'username' => (string) $project->getUsername(),
    ], $projects));
  }
}
