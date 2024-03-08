<?php

namespace App\DB\EntityRepository\Project;

use App\Admin\Tools\FeatureFlag\FeatureFlagManager;
use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\Project\Scratch\ScratchProgramRemixRelation;
use App\Utils\RequestHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elastica\Query\MatchQuery;
use FOS\ElasticaBundle\Finder\TransformedFinder;

class ProgramRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry, protected RequestHelper $app_request, protected FeatureFlagManager $feature_flag_manager, private readonly TransformedFinder $program_finder)
  {
    parent::__construct($managerRegistry, Program::class);
  }

  public function getProjectByUserID(string $user_id, bool $include_private = false): array
  {
    $query_builder = $this->createQueryBuilder('e');
    $query_builder
      ->where($query_builder->expr()->eq('e.user', $query_builder->expr()->literal($user_id)))
    ;

    $query_builder = $this->excludeInvisibleProjects($query_builder);
    $query_builder = $this->excludeDebugProjects($query_builder);

    if (!$include_private) {
      $query_builder = $this->excludePrivateProjects($query_builder);
    }

    return $query_builder->getQuery()->getResult();
  }

  public function getProjectByID(string $program_id, bool $include_private = false): array
  {
    $query_builder = $this->createQueryBuilder('e');
    $query_builder
      ->where($query_builder->expr()->eq('e.id', $query_builder->expr()->literal($program_id)))
    ;

    $query_builder = $this->excludeInvisibleProjects($query_builder);
    $query_builder = $this->excludeDebugProjects($query_builder);

    if (!$include_private) {
      $query_builder = $this->excludePrivateProjects($query_builder);
    }

    return $query_builder->getQuery()->getResult();
  }

  public function getProjects(?string $flavor = null, string $max_version = '', int $limit = 20, int $offset = 0, string $order_by = '', string $order = 'DESC'): array
  {
    if ($this->feature_flag_manager->isEnabled('GET_projects_elastica')) {
      $bool_query = new BoolQuery();
      $this->excludeUnavailableAndPrivateProjectsElastica($bool_query, $flavor, $max_version);
      $query = $this->buildElasticaQuery($bool_query, $order_by, $order);

      return $this->program_finder->find($query, $limit, ['from' => $offset]);
    }
    $query_builder = $this->createQueryAllBuilder();
    $query_builder = $this->excludeUnavailableAndPrivateProjects($query_builder, $flavor, $max_version);
    $query_builder = $this->setPagination($query_builder, $limit, $offset);
    $query_builder = $this->setOrderBy($query_builder, $order_by, $order);

    return $query_builder->getQuery()->getResult();
  }

  public function countProjects(?string $flavor = null, string $max_version = ''): int
  {
    $query_builder = $this->createQueryCountBuilder();
    $query_builder = $this->excludeUnavailableAndPrivateProjects($query_builder, $flavor, $max_version);

    return $this->getQueryCount($query_builder);
  }

  public function getTrendingProjects(?string $flavor = null, string $max_version = '', int $limit = 20, int $offset = 0, string $order_by = '', string $order = 'DESC'): array
  {
    $now = new \DateTime('now', new \DateTimeZone('UTC'));
    $time_for_check = $now->sub(new \DateInterval('P7D'))->format('Y-m-d H:i:s');
    if ($this->feature_flag_manager->isEnabled('GET_projects_elastica')) {
      $bool_query = new BoolQuery();
      $this->excludeUnavailableAndPrivateProjectsElastica($bool_query, $flavor, $max_version);
      $should_query = new BoolQuery();
      $should_query->addShould(new Query\Range('uploaded_at', ['gte' => $time_for_check]));
      $should_query->addShould(new Query\Range('last_modified_at', ['gte' => $time_for_check]));
      $bool_query->addMust($should_query);
      $query = $this->buildElasticaQuery($bool_query, $order_by, $order);

      return $this->program_finder->find($query, $limit, ['from' => $offset]);
    }
    $query_builder = $this->createQueryAllBuilder();
    $query_builder = $this->excludeUnavailableAndPrivateProjects($query_builder, $flavor, $max_version);
    $query_builder = $this->setPagination($query_builder, $limit, $offset);
    $query_builder = $this->setOrderBy($query_builder, $order_by, $order);
    $date_time_delta_7_days = $query_builder->expr()->literal($time_for_check);
    $left_or = $query_builder->expr()->gte('e.uploaded_at', $date_time_delta_7_days);
    $right_or = $query_builder->expr()->gte('e.last_modified_at', $date_time_delta_7_days);
    $query_builder->andWhere($query_builder->expr()->orX($left_or, $right_or));

    return $query_builder->getQuery()->getResult();
  }

  public function getScratchRemixProjects(?string $flavor = null, string $max_version = '', int $limit = 20, int $offset = 0): array
  {
    $qb = $this->createQueryAllBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb, $flavor, $max_version);
    $qb = $this->setPagination($qb, $limit, $offset);
    $qb = $this->setOrderBy($qb, 'views');
    $qb
      ->innerJoin(ScratchProgramRemixRelation::class, 'rp')
      ->andWhere($qb->expr()->eq('e.id', 'rp.catrobat_child'))
    ;

    return $qb->getQuery()->getResult();
  }

  public function countScratchRemixProjects(?string $flavor = null, string $max_version = ''): int
  {
    $qb = $this->createQueryCountBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb, $flavor, $max_version);
    $qb
      ->innerJoin(ScratchProgramRemixRelation::class, 'rp')
      ->where($qb->expr()->eq('e.id', 'rp.catrobat_child'))
    ;

    return $this->getQueryCount($qb);
  }

  public function getPublicUserProjects(string $user_id, ?string $flavor, string $max_version, ?int $limit, ?int $offset): array
  {
    $qb = $this->createQueryAllBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb, $flavor, $max_version);
    $qb = $this->setPagination($qb, $limit, $offset);
    $qb = $this->setOrderBy($qb, 'uploaded_at');
    $qb
      ->andWhere($qb->expr()->eq('e.user', ':user_id'))
      ->setParameter('user_id', $user_id)
    ;

    return $qb->getQuery()->getResult();
  }

  public function countPublicUserProjects(string $user_id, ?string $flavor = null, string $max_version = ''): int
  {
    $qb = $this->createQueryCountBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb, $flavor, $max_version);
    $qb
      ->andWhere($qb->expr()->eq('e.user', ':user_id'))
      ->setParameter('user_id', $user_id)
    ;

    return $this->getQueryCount($qb);
  }

  public function getUserProjectsIncludingPrivateOnes(string $user_id, ?string $flavor, string $max_version, ?int $limit, ?int $offset): array
  {
    $qb = $this->createQueryAllBuilder();
    $qb = $this->excludeUnavailableProjects($qb, $flavor, $max_version);
    $qb = $this->setPagination($qb, $limit, $offset);
    $qb = $this->setOrderBy($qb, 'uploaded_at');
    $qb
      ->andWhere($qb->expr()->eq('e.user', ':user_id'))
      ->setParameter('user_id', $user_id)
    ;

    return $qb->getQuery()->getResult();
  }

  public function countUserProjectsIncludingPrivateOnes(string $user_id, ?string $flavor = null, string $max_version = ''): int
  {
    $qb = $this->createQueryCountBuilder();
    $qb = $this->excludeUnavailableProjects($qb, $flavor, $max_version);
    $qb
      ->andWhere($qb->expr()->eq('e.user', ':user_id'))
      ->setParameter('user_id', $user_id)
    ;

    return $this->getQueryCount($qb);
  }

  public function getProjectsByTagInternalTitle(string $internal_title, ?int $limit = 20, ?int $offset = 0, ?string $flavor = null, string $max_version = ''): array
  {
    $qb = $this->createQueryAllBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb, $flavor, $max_version);
    $qb = $this->setPagination($qb, $limit, $offset);
    $qb = $this->setOrderBy($qb, 'uploaded_at');
    $qb
      ->leftJoin('e.tags', 'f')
      ->andWhere($qb->expr()->eq('f.internal_title', ':internal_title'))
      ->setParameter('internal_title', $internal_title)
    ;

    return $qb->getQuery()->getResult();
  }

  public function getProjectsByExtensionInternalTitle(string $internal_title, ?int $limit = 20, ?int $offset = 0): array
  {
    $qb = $this->createQueryAllBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb);
    $qb = $this->setPagination($qb, $limit, $offset);
    $qb = $this->setOrderBy($qb, 'uploaded_at');
    $qb
      ->leftJoin('e.extensions', 'f')
      ->andWhere($qb->expr()->eq('f.internal_title', ':internal_title'))
      ->setParameter('internal_title', $internal_title)
    ;

    return $qb->getQuery()->getResult();
  }

  public function searchTagCount(string $tag_name, ?string $flavor = null, string $max_version = ''): int
  {
    $qb = $this->createQueryCountBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb, $flavor, $max_version);
    $qb
      ->leftJoin('e.tags', 'f')
      ->andWhere($qb->expr()->eq('f.internal_title', ':internal_title'))
      ->setParameter('internal_title', $tag_name)
    ;

    return $this->getQueryCount($qb);
  }

  public function searchExtensionCount(string $extension_internal_title, ?string $flavor = null, string $max_version = ''): int
  {
    $qb = $this->createQueryCountBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb, $flavor, $max_version);
    $qb
      ->leftJoin('e.extensions', 'f')
      ->andWhere($qb->expr()->eq('f.internal_title', ':internal_title'))
      ->setParameter('internal_title', $extension_internal_title)
    ;

    return $this->getQueryCount($qb);
  }

  public function getMoreProjectsFromUser(string $user_id, string $project_id, ?string $flavor = null, string $max_version = '', ?int $limit = 20, ?int $offset = 0): array
  {
    $qb = $this->createQueryAllBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb, $flavor, $max_version);
    $qb = $this->setPagination($qb, $limit, $offset);
    $qb = $this->setOrderBy($qb, 'uploaded_at');
    $qb
      ->andWhere($qb->expr()->eq('e.user', ':user_id'))
      ->setParameter('user_id', $user_id)
      ->andWhere($qb->expr()->neq('e.id', ':project_id'))
      ->setParameter('project_id', $project_id)
    ;

    return $qb->getQuery()->getResult();
  }

  // -------------------------------------------------------------------------------------------------------------------
  //  Remix System
  //

  /**
   * @param string[] $program_ids
   *
   * @return string[]
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function filterExistingProgramIds(array $program_ids): array
  {
    $query_builder = $this->createQueryBuilder('p');

    $result = $query_builder
      ->select(['p.id'])
      ->where('p.id IN (:program_ids)')
      ->setParameter('program_ids', $program_ids)
      ->distinct()
      ->getQuery()
      ->getResult()
    ;

    return array_map(fn ($data) => $data['id'], $result);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function markAllProjectsAsNotYetMigrated(): void
  {
    $query_builder = $this->createQueryBuilder('p');

    $query_builder
      ->update()
      ->set('p.remix_migrated_at', ':remix_migrated_at')
      ->setParameter(':remix_migrated_at', null)
      ->getQuery()
      ->execute()
    ;
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findNext(string $previous_program_id): mixed
  {
    $query_builder = $this->createQueryBuilder('p');

    return $query_builder
      ->select('min(p.id)')
      ->where($query_builder->expr()->gt('p.id', ':previous_program_id'))
      ->setParameter('previous_program_id', $previous_program_id)
      ->distinct()
      ->getQuery()
      ->getSingleScalarResult()
    ;
  }

  public function getProjectDataByIds(array $program_ids): array
  {
    $query_builder = $this->createQueryBuilder('e');
    $query_builder = $this->excludeUnavailableAndPrivateProjects($query_builder);
    $query_builder
      ->select(['e.id', 'e.name', 'e.uploaded_at', 'u.username'])
      ->innerJoin('e.user', 'u')
      ->andWhere('e.id IN (:program_ids)')
      ->setParameter('program_ids', $program_ids)
      ->distinct()
    ;

    return $query_builder->getQuery()->getResult();
  }

  public function getMostLikedPrograms(?string $flavor, string $max_version, int $limit = 0, int $offset = 0): array
  {
    $query_builder = $this->createQueryBuilder('e');
    $query_builder = $this->excludeUnavailableAndPrivateProjects($query_builder, $flavor, $max_version);
    $query_builder = $this->setPagination($query_builder, $limit, $offset);

    $query_builder
      ->select(['e as program', 'COUNT(e.id) as like_count'])
      ->innerJoin(ProgramLike::class, 'l', Join::WITH,
        $query_builder->expr()->eq('e.id', 'l.program_id')->__toString())
      ->having($query_builder->expr()->gt('like_count', $query_builder->expr()->literal(1)))
      ->groupBy('e.id')
      ->orderBy('like_count', 'DESC')
      ->distinct()
    ;

    $results = $query_builder->getQuery()->getResult();

    return array_map(fn ($result) => $result['program'], $results);
  }

  public function getOtherMostDownloadedProjectsOfUsersThatAlsoDownloadedGivenProject(string $flavor, Program $program, ?int $limit, int $offset): array
  {
    return []; // disabled
  }

  public function filterVisiblePrograms(array $programs, string $max_version = ''): array
  {
    if (empty($programs)) {
      return [];
    }

    /** @var Program[] $filtered_programs */
    $filtered_programs = [];

    foreach ($programs as $program) {
      if (true === $program->getVisible() && false === $program->getPrivate()
        && ($this->app_request->isDebugBuildRequest() || false === $program->isDebugBuild())
        && ('' === $max_version || $program->getLanguageVersion() <= $max_version)) {
        $filtered_programs[] = $program;
      }
    }

    return $filtered_programs;
  }

  //
  // --------------------------------------------------------------------------------------------------------------------
  //
  private function createQueryAllBuilder(string $alias = 'e'): QueryBuilder
  {
    return $this->createQueryBuilder($alias)->select($alias);
  }

  private function createQueryCountBuilder(string $alias = 'e'): QueryBuilder
  {
    return $this->createQueryBuilder($alias)->select("count({$alias}.id)");
  }

  private function getQueryCount(QueryBuilder $query_builder): int
  {
    try {
      return intval($query_builder->getQuery()->getSingleScalarResult());
    } catch (NonUniqueResultException|NoResultException) {
      return 0;
    }
  }

  private function setOrderBy(QueryBuilder $query_builder, string $order_by = '', string $order = 'DESC', string $alias = 'e'): QueryBuilder
  {
    if ('' !== trim($order_by)) {
      $query_builder = $query_builder
        ->orderBy($alias.'.'.$order_by, $order)
      ;
    }

    return $query_builder;
  }

  private function setPagination(QueryBuilder $query_builder, ?int $limit, ?int $offset): QueryBuilder
  {
    if (null !== $offset && $offset > 0) {
      $query_builder->setFirstResult($offset);
    }
    if (null !== $limit && $limit > 0) {
      $query_builder->setMaxResults($limit);
    }

    return $query_builder;
  }

  private function excludeUnavailableAndPrivateProjects(QueryBuilder $qb, ?string $flavor = null, string $max_version = '', string $alias = 'e'): QueryBuilder
  {
    $qb = $this->excludeUnavailableProjects($qb, $flavor, $max_version, $alias);

    return $this->excludePrivateProjects($qb, $alias);
  }

  private function excludeUnavailableProjects(QueryBuilder $qb, ?string $flavor = null, string $max_version = '', string $alias = 'e'): QueryBuilder
  {
    $qb = $this->excludeInvisibleProjects($qb, $alias);
    $qb = $this->excludeDebugProjects($qb, $alias);
    $qb = $this->setFlavorConstraint($qb, $flavor, $alias);

    return $this->excludeProjectsWithTooHighLanguageVersion($qb, $max_version, $alias);
  }

  private function setFlavorConstraint(QueryBuilder $query_builder, ?string $flavor = null, string $alias = 'e'): QueryBuilder
  {
    if ('' === trim($flavor)) {
      return $query_builder;
    }

    if ('!' === $flavor[0]) {
      // Can be used when we explicitly want projects of other flavors (E.g to fill empty categories of a new flavor)
      return $query_builder
        ->andWhere($query_builder->expr()->neq($alias.'.flavor', ':flavor'))
        ->setParameter('flavor', substr((string) $flavor, 1))
      ;
    }

    // Extensions are very similar to Flavors. (E.g. it does not care if a project has embroidery flavor or extension)
    return $query_builder->leftJoin($alias.'.extensions', 'ext')
      ->andWhere($query_builder->expr()->orX()->addMultiple([
        $query_builder->expr()->like('lower('.$alias.'.flavor)', ':flavor'),
        $query_builder->expr()->like('lower(ext.internal_title)', ':extension'),
      ]))
      ->setParameter('flavor', strtolower((string) $flavor))
      ->setParameter('extension', strtolower((string) $flavor))
    ;
  }

  private function excludeProjectsWithTooHighLanguageVersion(QueryBuilder $query_builder, string $max_version = '', string $alias = 'e'): QueryBuilder
  {
    if ('' !== $max_version) {
      $query_builder
        ->andWhere($query_builder->expr()->lte($alias.'.language_version', ':max_version'))
        ->setParameter('max_version', $max_version)
      ;
    }

    return $query_builder;
  }

  private function excludeDebugProjects(QueryBuilder $query_builder, string $alias = 'e'): QueryBuilder
  {
    if (!$this->app_request->isDebugBuildRequest() && 'dev' !== $_ENV['APP_ENV']) {
      $query_builder->andWhere(
        $query_builder->expr()->eq($alias.'.debug_build', $query_builder->expr()->literal(false))
      );
    }

    return $query_builder;
  }

  private function excludeInvisibleProjects(QueryBuilder $query_builder, string $alias = 'e'): QueryBuilder
  {
    return $query_builder->andwhere(
      $query_builder->expr()->eq($alias.'.visible', $query_builder->expr()->literal(true))
    );
  }

  private function excludePrivateProjects(QueryBuilder $query_builder, string $alias = 'e'): QueryBuilder
  {
    return $query_builder->andWhere(
      $query_builder->expr()->eq($alias.'.private', $query_builder->expr()->literal(false))
    );
  }

  private function excludeUnavailableAndPrivateProjectsElastica(BoolQuery $qb, ?string $flavor = null, string $max_version = ''): void
  {
    $this->excludePrivateProjectsElastica($qb);
    $this->excludeUnavailableProjectsElastica($qb, $flavor, $max_version);
  }

  private function excludeUnavailableProjectsElastica(BoolQuery $qb, ?string $flavor = null, string $max_version = ''): void
  {
    $this->excludeInvisibleProjectsElastica($qb);
    $this->excludeDebugProjectsElastica($qb);
    $this->setFlavorConstraintElastica($qb, $flavor);
    $this->excludeProjectsWithTooHighLanguageVersionElastica($qb, $max_version);
  }

  private function setFlavorConstraintElastica(BoolQuery $qb, ?string $flavor = null): void
  {
    if ('' === trim($flavor)) {
      return;
    }
    if ('!' === $flavor[0]) {
      $must_not = new BoolQuery();
      $must_not->addMustNot(new MatchQuery('flavor', strtolower(substr($flavor, 1))));
      $qb->addMust($must_not);
    }
    $should = new BoolQuery();
    $should->addShould(new Query\Wildcard('flavor', strtolower($flavor)));
    $should->addShould(new Query\Wildcard('getExtensionsString', strtolower($flavor)));
    $qb->addMust($should);
  }

  private function excludeProjectsWithTooHighLanguageVersionElastica(BoolQuery $qb, string $max_version = ''): void
  {
    if ('' !== $max_version) {
      $qb->addMust(new Query\Range('language_version', ['lte' => $max_version]));
    }
  }

  private function excludeDebugProjectsElastica(BoolQuery $qb): void
  {
    if (!$this->app_request->isDebugBuildRequest() && 'dev' !== $_ENV['APP_ENV']) {
      $qb->addMust(new Query\Term(['debug_build' => false]));
    }
  }

  private function excludeInvisibleProjectsElastica(BoolQuery $qb): void
  {
    $qb->addMust(new Query\Term(['visible' => true]));
  }

  private function excludePrivateProjectsElastica(BoolQuery $qb): void
  {
    $qb->addMust(new Query\Term(['private' => false]));
  }

  private function buildElasticaQuery(BoolQuery $bool_query, string $order_by, string $order): Query
  {
    $query = new Query();
    $query->setQuery($bool_query);
    if ('' != $order_by) {
      $query->addSort([$order_by => $order]);
    }

    return $query;
  }

  public function findOneByName(string $programName): ?Program
  {
    return $this->findOneBy(['name' => $programName]);
  }
}
