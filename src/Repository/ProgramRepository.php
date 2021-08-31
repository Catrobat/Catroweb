<?php

namespace App\Repository;

use App\Catrobat\Requests\AppRequest;
use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramLike;
use App\Entity\ScratchProgramRemixRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ProgramRepository extends ServiceEntityRepository
{
  protected AppRequest $app_request;

  public function __construct(ManagerRegistry $managerRegistry, AppRequest $app_request)
  {
    parent::__construct($managerRegistry, Program::class);
    $this->app_request = $app_request;
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
      ->leftJoin('e.user', 'f')
      ->andWhere($qb->expr()->eq('f.id', ':user_id'))
      ->setParameter('user_id', $user_id)
    ;

    return $qb->getQuery()->getResult();
  }

  public function countPublicUserProjects(string $user_id, ?string $flavor = null, string $max_version = ''): int
  {
    $qb = $this->createQueryCountBuilder();
    $qb = $this->excludeUnavailableAndPrivateProjects($qb, $flavor, $max_version);
    $qb
      ->leftJoin('e.user', 'f')
      ->andWhere($qb->expr()->eq('f.id', ':user_id'))
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
      ->leftJoin('e.user', 'f')
      ->andWhere($qb->expr()->eq('f.id', ':user_id'))
      ->setParameter('user_id', $user_id)
    ;

    return $qb->getQuery()->getResult();
  }

  public function countUserProjectsIncludingPrivateOnes(string $user_id, ?string $flavor = null, string $max_version = ''): int
  {
    $qb = $this->createQueryCountBuilder();
    $qb = $this->excludeUnavailableProjects($qb, $flavor, $max_version);
    $qb
      ->leftJoin('e.user', 'f')
      ->andWhere($qb->expr()->eq('f.id', ':user_id'))
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
      ->leftJoin('e.user', 'f')
      ->andWhere($qb->expr()->eq('f.id', ':user_id'))
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
  public function markAllProgramsAsNotYetMigrated(): void
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
   * @return mixed
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findNext(string $previous_program_id)
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

  // -------------------------------------------------------------------------------------------------------------------
  //  Recommender System
  //
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

  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(string $flavor, Program $program, ?int $limit, int $offset): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select(['e as program', 'COUNT(e.id) as user_download_count'])
      ->innerJoin(
        ProgramDownloads::class, 'd1',
        Join::WITH, $query_builder->expr()->eq('e.id', 'd1.program')->__toString()
      )
      ->innerJoin(
        ProgramDownloads::class, 'd2',
        Join::WITH, $query_builder->expr()->eq('d1.user', 'd2.user')->__toString()
      )
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->isNotNull('d1.user'))
      ->andWhere($query_builder->expr()->neq('d1.user', ':user'))
      ->andWhere($query_builder->expr()->neq('d1.program', 'd2.program'))
      ->andWhere($query_builder->expr()->eq('d2.program', ':program'))
    ;

    $query_builder = $this->excludePrivateProjects($query_builder);
    $query_builder = $this->setFlavorConstraint($query_builder, $flavor);
    $query_builder = $this->excludeDebugProjects($query_builder);

    $query_builder
      ->groupBy('e.id')
      ->orderBy('user_download_count', 'DESC')
      ->setParameter('user', $program->getUser())
      ->setParameter('program', $program)
      ->distinct()
    ;

    if ($offset > 0) {
      $query_builder->setFirstResult($offset);
    }

    if (!is_null($limit) && $limit > 0) {
      $query_builder->setMaxResults($limit);
    }

    $results = $query_builder->getQuery()->getResult();

    return array_map(fn ($result) => $result['program'], $results);
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

  public function getRecommendedProgramsCount(string $id, string $flavor = 'pocketcode'): int
  {
    $db_query = $this->createRecommendedProgramQuery($id, $flavor);

    return is_countable($db_query->getResult()) ? count($db_query->getResult()) : 0;
  }

  public function getRecommendedProgramsById(string $id, string $flavor, ?int $limit, ?int $offset): array
  {
    $db_query = $this->createRecommendedProgramQuery($id, $flavor);

    $db_query->setFirstResult($offset);
    $db_query->setMaxResults($limit);

    $id_list = array_map(fn ($value) => $value['id'], $db_query->getResult());

    $programs = [];
    foreach ($id_list as $id) {
      $programs[] = $this->find($id);
    }

    return $programs;
  }

  private function createRecommendedProgramQuery(string $id, string $flavor): Query
  {
    $qb_tags = $this->createQueryBuilder('e');

    $result = $qb_tags
      ->select('t.id')
      ->leftJoin('e.tags', 't')
      ->where($qb_tags->expr()->eq('e.id', ':id'))
      ->setParameter('id', $id)
      ->getQuery()
      ->getResult()
    ;

    $qb_extensions = $this->createQueryBuilder('e');

    $result_2 = $qb_extensions
      ->select('x.id')
      ->leftJoin('e.extensions', 'x')
      ->where($qb_tags->expr()->eq('e.id', ':id'))
      ->setParameter('id', $id)
      ->getQuery()
      ->getResult()
    ;

    $tag_ids = array_map('current', $result);
    $extensions_id = array_map('current', $result_2);

    $debug_where = (!$this->app_request->isDebugBuildRequest() && 'dev' !== $_ENV['APP_ENV']) ? 'AND e.debug_build = false' : '';
    $flavor_where = '' !== trim($flavor) ? 'AND e.flavor = :flavor' : '';

    $dql = 'SELECT COUNT(e.id) cnt, e.id
      FROM App\Entity\Program e
      LEFT JOIN e.tags t
      LEFT JOIN e.extensions x
      WHERE (
        t.id IN (:tag_ids)
        OR x.id IN (:extension_ids)
      ) '
      .$flavor_where.'
      AND e.id != :id
      AND e.private = false
      AND e.visible = TRUE '
      .$debug_where.'  
      GROUP BY e.id
      ORDER BY cnt DESC';

    $qb_program = $this->createQueryBuilder('e');
    $db_query = $qb_program->getEntityManager()->createQuery($dql);

    $db_query->setParameter('id', $id);
    $db_query->setParameter('tag_ids', $tag_ids);
    $db_query->setParameter('extension_ids', $extensions_id);

    if ('' !== trim($flavor_where)) {
      $db_query->setParameter('flavor', $flavor);
    }

    return $db_query;
  }

  //
  //--------------------------------------------------------------------------------------------------------------------
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
    } catch (NoResultException|NonUniqueResultException $e) {
      return 0;
    }
  }

  private function setOrderBy(QueryBuilder $query_builder, string $order_by = '', string $order = 'DESC', string $alias = 'e'): QueryBuilder
  {
    if ('' === trim($order_by)) {
      return $query_builder;
    }

    if ('RAND()' === $order_by) {
      return $query_builder
        ->orderBy($order_by, $order)
        ;
    }

    return $query_builder
      ->orderBy($alias.'.'.$order_by, $order)
    ;
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
        ->setParameter('flavor', substr($flavor, 1))
        ;
    }

    // Extensions are very similar to Flavors. (E.g. it does not care if a project has embroidery flavor or extension)
    return $query_builder->leftJoin($alias.'.extensions', 'ext')
      ->andWhere($query_builder->expr()->orX()->addMultiple([
        $query_builder->expr()->like('lower('.$alias.'.flavor)', ':flavor'),
        $query_builder->expr()->like('lower(ext.internal_title)', ':extension'),
      ]))
      ->setParameter('flavor', strtolower($flavor))
      ->setParameter('extension', strtolower($flavor))
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
}
