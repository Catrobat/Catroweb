<?php

namespace App\Repository;

use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramLike;
use App\Entity\ScratchProgramRemixRelation;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

class ProgramRepository extends ServiceEntityRepository
{
  private array $cached_most_remixed_programs_full_result = [];

  private array $cached_most_liked_programs_full_result = [];

  private array $cached_most_downloaded_other_programs_full_result = [];

  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Program::class);
  }

  public function getMostDownloadedPrograms(bool $debug_build, string $flavor = null, int $limit = 20, int $offset = 0,
                                            string $max_version = '0'): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder->select('e')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('e.downloads', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  public function getMostDownloadedProgramsCount(bool $debug_build, string $flavor = null, string $max_version = '0'): int
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder->select('count(e.id)')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('e.downloads', 'DESC')
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    try
    {
      $projects_count = $query_builder->getQuery()->getSingleScalarResult();
    }
    catch (NoResultException | NonUniqueResultException $e)
    {
      $projects_count = 0;
    }

    return $projects_count;
  }

  public function getScratchRemixesPrograms(bool $debug_build, string $flavor = null, int $limit = 20, int $offset = 0,
                                            string $max_version = '0'): array
  {
    $qb = $this->createQueryBuilder('e');

    $qb
      ->select('e')
      ->where($qb->expr()->eq('e.visible', $qb->expr()->literal(true)))
      ->andWhere($qb->expr()->eq('e.private', $qb->expr()->literal(false)))
      ->innerJoin(ScratchProgramRemixRelation::class, 'rp')
      ->where($qb->expr()->eq('e.id', 'rp.catrobat_child'))
      ->orderBy('e.views', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $qb = $this->addDebugBuildCondition($qb, $debug_build);
    $qb = $this->addFlavorCondition($qb, $flavor);
    $qb = $this->addMaxVersionCondition($qb, $max_version);

    return $qb->getQuery()->getResult();
  }

  public function getScratchRemixesProgramsCount(bool $debug_build, string $flavor = null, string $max_version = '0'): int
  {
    $qb = $this->createQueryBuilder('e');

    $qb
      ->select('count(e.id)')
      ->where($qb->expr()->eq('e.visible', $qb->expr()->literal(true)))
      ->andWhere($qb->expr()->eq('e.private', $qb->expr()->literal(false)))
      ->innerJoin(ScratchProgramRemixRelation::class, 'rp')
      ->where($qb->expr()->eq('e.id', 'rp.catrobat_child'))
      ->orderBy('e.views', 'DESC')
    ;

    $qb = $this->addDebugBuildCondition($qb, $debug_build);
    $qb = $this->addFlavorCondition($qb, $flavor);
    $qb = $this->addMaxVersionCondition($qb, $max_version);

    try
    {
      $projects_count = $qb->getQuery()->getSingleScalarResult();
    }
    catch (NoResultException | NonUniqueResultException $e)
    {
      $projects_count = 0;
    }

    return $projects_count;
  }

  public function getMostViewedPrograms(bool $debug_build, string $flavor = null, int $limit = 20, int $offset = 0,
                                        string $max_version = '0'): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('e.views', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  public function getMostViewedProgramsCount(bool $debug_build, string $flavor = null, string $max_version = '0'): int
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('count(e.id)')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('e.views', 'DESC')
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    try
    {
      $projects_count = $query_builder->getQuery()->getSingleScalarResult();
    }
    catch (NoResultException | NonUniqueResultException $e)
    {
      $projects_count = 0;
    }

    return $projects_count;
  }

  /**
   * @throws DBALException
   */
  public function getMostRemixedPrograms(bool $debug_build, string $flavor = 'pocketcode', ?int $limit = 20, int $offset = 0): array
  {
    if (!isset($this->cached_most_remixed_programs_full_result[$flavor]))
    {
      $connection = $this->getEntityManager()->getConnection();
      $sql = $this->generateUnionSqlStatementForMostRemixedPrograms($debug_build, $limit, $offset);
      $statement = $connection->prepare($sql);
      $statement->bindValue('flavor', $flavor);
      $statement->execute();
      $results = $statement->fetchAll();
    }
    else
    {
      $results = array_slice($this->cached_most_remixed_programs_full_result[$flavor],
        $offset, $limit);
    }

    $programs = [];
    foreach ($results as $result)
    {
      $programs[] = $this->find($result['id']);
    }

    return $programs;
  }

  /**
   * @throws DBALException
   */
  public function getTotalRemixedProgramsCount(bool $debug_build, string $flavor = 'pocketcode'): int
  {
    if (isset($this->cached_most_remixed_programs_full_result[$flavor]))
    {
      return count($this->cached_most_remixed_programs_full_result[$flavor]);
    }

    $connection = $this->getEntityManager()->getConnection();
    $statement = $connection->prepare($this->generateUnionSqlStatementForMostRemixedPrograms($debug_build, 0, 0));
    $statement->bindValue('flavor', $flavor);
    $statement->execute();

    $this->cached_most_remixed_programs_full_result[$flavor] = $statement->fetchAll();

    return is_countable($this->cached_most_remixed_programs_full_result[$flavor]) ? count($this->cached_most_remixed_programs_full_result[$flavor]) : 0;
  }

  /**
   * @return Program[]
   */
  public function getMostLikedPrograms(bool $debug_build, string $flavor = 'pocketcode', ?int $limit = 20, int $offset = 0): array
  {
    if (isset($this->cached_most_liked_programs_full_result[$flavor]))
    {
      return array_slice($this->cached_most_liked_programs_full_result[$flavor], $offset, $limit);
    }

    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select(['e as program', 'COUNT(e.id) as like_count'])
      ->innerJoin(ProgramLike::class, 'l', Join::WITH,
        $query_builder->expr()->eq('e.id', 'l.program_id'))
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->having($query_builder->expr()->gt('like_count', $query_builder->expr()->literal(1)))
      ->groupBy('e.id')
      ->orderBy('like_count', 'DESC')
      ->distinct()
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    if ((int) $offset > 0)
    {
      $query_builder->setFirstResult($offset);
    }

    if ((int) $limit > 0)
    {
      $query_builder->setMaxResults($limit);
    }

    $results = $query_builder->getQuery()->getResult();

    return array_map(fn ($result) => $result['program'], $results);
  }

  public function getTotalLikedProgramsCount(bool $debug_build, string $flavor = 'pocketcode'): int
  {
    if (isset($this->cached_most_liked_programs_full_result[$flavor]))
    {
      return count($this->cached_most_liked_programs_full_result[$flavor]);
    }

    $this->cached_most_liked_programs_full_result[$flavor] =
      $this->getMostLikedPrograms($debug_build, $flavor, 0, 0);

    return is_countable($this->cached_most_liked_programs_full_result[$flavor]) ? count($this->cached_most_liked_programs_full_result[$flavor]) : 0;
  }

  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
    bool $debug_build, string $flavor, Program $program, ?int $limit, int $offset): array
  {
    $cache_key = $flavor.'_'.$program->getId();
    if (isset($this->cached_most_downloaded_other_programs_full_result[$cache_key]))
    {
      return array_slice($this->cached_most_downloaded_other_programs_full_result[$cache_key],
        $offset, $limit);
    }

    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select(['e as program', 'COUNT(e.id) as user_download_count'])
      ->innerJoin(
        ProgramDownloads::class, 'd1',
        Join::WITH, $query_builder->expr()->eq('e.id', 'd1.program')
      )
      ->innerJoin(
        ProgramDownloads::class, 'd2',
        Join::WITH, $query_builder->expr()->eq('d1.user', 'd2.user')
      )
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->isNotNull('d1.user'))
      ->andWhere($query_builder->expr()->neq('d1.user', ':user'))
      ->andWhere($query_builder->expr()->neq('d1.program', 'd2.program'))
      ->andWhere($query_builder->expr()->eq('d2.program', ':program'))
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    $query_builder
      ->groupBy('e.id')
      ->orderBy('user_download_count', 'DESC')
      ->setParameter('user', $program->getUser())
      ->setParameter('program', $program)
      ->distinct()
    ;

    if ((int) $offset > 0)
    {
      $query_builder->setFirstResult($offset);
    }

    if ((int) $limit > 0)
    {
      $query_builder->setMaxResults($limit);
    }

    $results = $query_builder->getQuery()->getResult();

    return array_map(fn ($result) => $result['program'], $results);
  }

  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount(
    bool $debug_build, string $flavor, Program $program): int
  {
    $cache_key = $flavor.'_'.$program->getId();
    if (isset($this->cached_most_downloaded_other_programs_full_result[$cache_key]))
    {
      return count($this->cached_most_downloaded_other_programs_full_result[$cache_key]);
    }

    $result = $this->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
      $debug_build, $flavor, $program, 0, 0
    );
    $this->cached_most_downloaded_other_programs_full_result[$cache_key] = $result;

    return is_countable($this->cached_most_downloaded_other_programs_full_result[$cache_key]) ? count($this->cached_most_downloaded_other_programs_full_result[$cache_key]) : 0;
  }

  public function getRecentPrograms(bool $debug_build, string $flavor = null, int $limit = 20, int $offset = 0,
                                    string $max_version = '0'): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('e.uploaded_at', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  public function getRecentProgramsCount(bool $debug_build, string $flavor = null, string $max_version = '0'): int
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('count(e.id)')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('e.uploaded_at', 'DESC')
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    try
    {
      $projects_count = $query_builder->getQuery()->getSingleScalarResult();
    }
    catch (NoResultException | NonUniqueResultException $e)
    {
      $projects_count = 0;
    }

    return $projects_count;
  }

  /**
   * @return Program[]
   */
  public function getExamplePrograms(bool $debug_build, ?string $flavor = null, ?int $limit = 20, int $offset = 0, string $max_version = '0'): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->where($query_builder->expr()->eq('e.example', $query_builder->expr()->literal(true)))
      ->orderBy('e.uploaded_at', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  public function getRandomPrograms(bool $debug_build, string $flavor = null, int $limit = 20, int $offset = 0,
                                    string $max_version = '0'): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('RAND()')
      ->setMaxResults($limit)
      ->setFirstResult($offset)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  public function getRandomProgramsCount(bool $debug_build, string $flavor = null, string $max_version = '0'): int
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('count(e.id)')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('RAND()')
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    try
    {
      $projects_count = $query_builder->getQuery()->getSingleScalarResult();
    }
    catch (NoResultException | NonUniqueResultException $e)
    {
      $projects_count = 0;
    }

    return $projects_count;
  }

  public function getUserPublicPrograms(string $user_id, bool $debug_build, string $max_version,
                                        int $limit, int $offset, string $flavor = null): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.user', 'f')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('f.id', ':user_id'))
      ->setParameter('user_id', $user_id)
      ->orderBy('e.uploaded_at', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  public function getUserPublicProgramsCount(string $user_id, bool $debug_build, string $max_version,
                                        string $flavor = null): int
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('count(e.id)')
      ->leftJoin('e.user', 'f')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('f.id', ':user_id'))
      ->setParameter('user_id', $user_id)
      ->orderBy('e.uploaded_at', 'DESC')
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    try
    {
      $projects_count = $query_builder->getQuery()->getSingleScalarResult();
    }
    catch (NoResultException | NonUniqueResultException $e)
    {
      $projects_count = 0;
    }

    return $projects_count;
  }

  public static function filterVisiblePrograms(array $programs, bool $debug_build, string $max_version = '0'): array
  {
    if (!is_array($programs) || 0 === count($programs))
    {
      return [];
    }

    /** @var Program[] $filtered_programs */
    $filtered_programs = [];

    foreach ($programs as $program)
    {
      if (true === $program->getVisible() && false === $program->getPrivate() &&
        ($debug_build || false === $program->isDebugBuild()) &&
        ('0' === $max_version || $max_version <= $program->getLanguageVersion()))
      {
        $filtered_programs[] = $program;
      }
    }

    return $filtered_programs;
  }

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

  public function search(string $query, bool $debug_build, ?int $limit = 10, int $offset = 0,
                         string $max_version = '0', ?string $flavor = null): array
  {
    $parse_languages = $this->getLanguageQuery();

    $search_terms = $this->getWordsQuery($query);

    $final_query = $this->getSearchQuery($parse_languages, $search_terms)
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $this->setScore($final_query, $query);

    $final_query = $this->addFlavorCondition($final_query, $flavor);
    $final_query = $this->addMaxVersionCondition($final_query, $max_version);
    $final_query = $this->addPrivacyCheckCondition($final_query);
    $final_query = $this->addDebugBuildCondition($final_query, $debug_build);

    $result = $final_query->getQuery()->getResult();

    return array_map(function ($element)
    {
      return $element[0];
    }, $result);
  }

  public function searchCount(string $query, bool $debug_build,
                              string $max_version = '0', ?string $flavor = null): int
  {
    $parse_languages = $this->getLanguageQuery();

    $search_terms = $this->getWordsQuery($query);

    $final_query = $this->getSearchQuery($parse_languages, $search_terms);

    $this->setScore($final_query, $query);

    $final_query = $this->addFlavorCondition($final_query, $flavor);
    $final_query = $this->addMaxVersionCondition($final_query, $max_version);
    $final_query = $this->addPrivacyCheckCondition($final_query);
    $final_query = $this->addDebugBuildCondition($final_query, $debug_build);

    $result = $final_query->getQuery()->getResult();

    return count($result);
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

  public function getUserPrograms(string $user_id, bool $debug_build, string $max_version): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.user', 'f')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('f.id', ':user_id'))
      ->setParameter('user_id', $user_id)
      ->orderBy('e.uploaded_at', 'DESC')
    ;

    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  public function getUserProjects(?string $username, ?int $limit, int $offset, ?string $flavor, bool $debug_build,
                                      string $max_version): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.user', 'f')
      ->where($query_builder->expr()->eq('f.username', ':username'))
      ->setParameter('username', $username)
      ->orderBy('e.uploaded_at', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  public function getUserProjectsCount(?string $username, ?string $flavor, bool $debug_build,
                                  string $max_version): int
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('count(e.id)')
      ->leftJoin('e.user', 'f')
      ->where($query_builder->expr()->eq('f.username', ':username'))
      ->setParameter('username', $username)
      ->orderBy('e.uploaded_at', 'DESC')
    ;

    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    try
    {
      $projects_count = $query_builder->getQuery()->getSingleScalarResult();
    }
    catch (NoResultException | NonUniqueResultException $e)
    {
      $projects_count = 0;
    }

    return $projects_count;
  }

  public function getPublicUserPrograms(?string $user_id, bool $debug_build, string $max_version = '0'): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.user', 'f')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('f.id', ':user_id'))
      ->setParameter('user_id', $user_id)
      ->orderBy('e.uploaded_at', 'DESC')
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  /**
   * @throws NonUniqueResultException
   * @throws NoResultException
   */
  public function getTotalPrograms(bool $debug_build, ?string $flavor = null, string $max_version = '0'): int
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('COUNT (e.id)')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return (int) $query_builder->getQuery()->getSingleScalarResult();
  }

  /**
   * @return mixed
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function getProgramsWithExtractedDirectoryHash()
  {
    $query_builder = $this->createQueryBuilder('e');

    return $query_builder
      ->select('e')
      ->where($query_builder->expr()->isNotNull('e.directory_hash'))
      ->getQuery()
      ->getResult()
    ;
  }

  public function getProgramsByTagId(int $id, bool $debug_build, ?int $limit = 20, int $offset = 0): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.tags', 'f')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('f.id', ':id'))
      ->orderBy('e.uploaded_at', 'DESC')
      ->setParameter('id', $id)
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    return $query_builder
      ->getQuery()
      ->getResult()
    ;
  }

  public function getProgram(string $program_id, bool $debug_build): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->where($query_builder->expr()->eq('e.id',
        $query_builder->expr()->literal($program_id)))
      ->andWhere($query_builder->expr()->eq('e.visible',
        $query_builder->expr()->literal(true)))
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    return $query_builder
      ->getQuery()
      ->getResult()
      ;
  }

  public function getProgramDataByIds(array $program_ids, bool $debug_build): array
  {
    $query_builder = $this->createQueryBuilder('p');

    $query_builder
      ->select(['p.id', 'p.name', 'p.uploaded_at', 'u.username'])
      ->innerJoin('p.user', 'u')
      ->where($query_builder->expr()->eq('p.visible',
        $query_builder->expr()->literal(true)))
      ->andWhere('p.id IN (:program_ids)')
      ->setParameter('program_ids', $program_ids)
      ->distinct()
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder, 'p');
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build, 'p');

    return $query_builder
      ->getQuery()
      ->getResult()
    ;
  }

  public function getProgramsByExtensionName(string $name, bool $debug_build,
                                             ?int $limit = 20, int $offset = 0): array
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.extensions', 'f')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('f.name', ':name'))
      ->orderBy('e.uploaded_at', 'DESC')
      ->setParameter('name', $name)
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    return $query_builder
      ->getQuery()
      ->getResult()
    ;
  }

  public function searchTagCount(string $query, bool $debug_build): int
  {
    $query = str_replace('yahoo', '', $query);

    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.tags', 't')
      ->where($query_builder->expr()->eq('e.visible',
        $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('t.id', ':id'))
      ->setParameter('id', $query)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    $result = $query_builder
      ->getQuery()
      ->getResult()
    ;

    return is_countable($result) ? count($result) : 0;
  }

  public function searchExtensionCount(string $query, bool $debug_build): int
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.extensions', 't')
      ->where($query_builder->expr()->eq('e.visible',
        $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('t.name', ':name'))
      ->setParameter('name', $query)
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    $result = $query_builder
      ->getQuery()
      ->getResult()
    ;

    return is_countable($result) ? count($result) : 0;
  }

  public function getRecommendedProgramsCount(string $id, bool $debug_build, string $flavor = 'pocketcode'): int
  {
    $db_query = $this->createRecommendedProgramQuery($id, $debug_build, $flavor);

    return is_countable($db_query->getResult()) ? count($db_query->getResult()) : 0;
  }

  public function getRecommendedProgramsById(string $id, bool $debug_build, string $flavor, ?int $limit, int $offset = 0): array
  {
    $db_query = $this->createRecommendedProgramQuery($id, $debug_build, $flavor);

    $db_query->setFirstResult($offset);
    $db_query->setMaxResults($limit);

    $id_list = array_map(fn ($value) => $value['id'], $db_query->getResult());

    $programs = [];
    foreach ($id_list as $id)
    {
      $programs[] = $this->find($id);
    }

    return $programs;
  }

  private function createRecommendedProgramQuery(string $id, bool $debug_build, string $flavor): Query
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

    $debug_where = (!$debug_build) ? 'AND e.debug_build = false' : '';

    $dql = 'SELECT COUNT(e.id) cnt, e.id
      FROM App\Entity\Program e
      LEFT JOIN e.tags t
      LEFT JOIN e.extensions x
      WHERE (
        t.id IN (:tag_ids)
        OR x.id IN (:extension_ids)
      )
      AND e.flavor = :flavor
      AND e.id != :id
      AND e.private = false
      AND e.visible = TRUE '.
      $debug_where.'  
      GROUP BY e.id
      ORDER BY cnt DESC';

    $qb_program = $this->createQueryBuilder('e');
    $db_query = $qb_program->getEntityManager()->createQuery($dql);

    $parameters = new ArrayCollection();
    $parameters->add(new Parameter('id', $id));
    $parameters->add(new Parameter('tag_ids', $tag_ids));
    $parameters->add(new Parameter('extension_ids', $extensions_id));
    $parameters->add(new Parameter('flavor', $flavor));

    $db_query->setParameters($parameters);

    return $db_query;
  }

  /**
   * @return string
   */
  private function getLanguageQuery()
  {
    $em = $this->getEntityManager();
    $metadata = $em->getClassMetadata('App\Entity\Tag')->getFieldNames();
    array_shift($metadata);

    $parse_languages = '';
    foreach ($metadata as $language)
    {
      $parse_languages .= 't.'.$language.', ';
    }

    return rtrim($parse_languages, ', ');
  }

  private function getWordsQuery(string $query): string
  {
    $words = explode(' ', $query);
    foreach ($words as &$word)
    {
      $word = '+'.$word.'*';
    }
    unset($word);
    $search_terms = implode(' ', $words);

    return $search_terms;
  }

  private function getSearchQuery(string $parse_languages, string $search_terms): QueryBuilder
  {
    return $this->createQueryBuilder('e')
      ->addSelect('MATCH(e.name, e.description, e.credits, e.id) AGAINST(:searchterm boolean) AS name_score')
      ->addSelect('MATCH(u.username) AGAINST(:searchterm boolean) AS username_score')
      ->addSelect('MATCH(x.name) AGAINST(:searchterm boolean) AS extension_score')
      ->addSelect('MATCH('.$parse_languages.') AGAINST(:searchterm boolean) as language_score')
      ->orWhere('MATCH(e.name, e.description, e.credits, e.id) AGAINST(:searchterm boolean)>:score')
      ->orWhere('MATCH(u.username) AGAINST(:searchterm boolean)>:score')
      ->orWhere('MATCH(x.name) AGAINST(:searchterm boolean)>:score')
      ->orWhere('MATCH('.$parse_languages.') AGAINST(:searchterm boolean)>:score')
      ->leftJoin('e.tags', 't')
      ->leftJoin('e.extensions', 'x')
      ->leftJoin('e.user', 'u')
      ->andWhere('e.visible = true AND e.private = false')
      ->orderBy('name_score', 'DESC')
      ->setParameter('searchterm', $search_terms)
    ;
  }

  private function setScore(QueryBuilder &$final_query, string $query): void
  {
    $words = explode(' ', $query);
    $number_of_words = count($words);
    $wanted_score = 0;
    switch ($number_of_words)
    {
      case 1:
        $wanted_score = 0;
        break;
      case 2:
        $wanted_score = 0.4;
        break;
      default:
        $wanted_score = 0.8;
    }
    $final_query->setParameter('score', $wanted_score);
  }

  private function generateUnionSqlStatementForMostRemixedPrograms(bool $debug_build,
                                                                   ?int $limit, int $offset = 0): string
  {
    //---------------------------------------------------------------------------------------------
    // ATTENTION: since Doctrine does not support UNION queries,
    //            the following query is a native MySQL/SQLite query
    //---------------------------------------------------------------------------------------------
    $limit_clause = (int) $limit > 0 ? 'LIMIT '.(int) $limit : '';
    $offset_clause = (int) $offset > 0 ? 'OFFSET '.(int) $offset : '';
    $debug_where = (!$debug_build) ? 'AND p.debug_build = false' : '';

    return '
            SELECT sum(remixes_count) AS total_remixes_count, id FROM (
                    SELECT p.id AS id, COUNT(p.id) AS remixes_count
                    FROM program p
                    INNER JOIN program_remix_relation r
                    ON p.id = r.ancestor_id
                    INNER JOIN program rp
                    ON r.descendant_id = rp.id
                    WHERE p.visible = 1 '.
      $debug_where.' 
                    AND p.flavor = :flavor
                    AND p.private = 0
                    AND r.depth = 1
                    AND p.user_id <> rp.user_id
                    GROUP BY p.id
                UNION ALL
                    SELECT p.id AS id, COUNT(p.id) AS remixes_count
                    FROM program p
                    INNER JOIN program_remix_backward_relation b
                    ON p.id = b.parent_id
                    INNER JOIN program rp
                    ON b.child_id = rp.id
                    WHERE p.visible = 1 '.
      $debug_where.' 
                    AND p.flavor = :flavor
                    AND p.private = 0
                    AND p.user_id <> rp.user_id
                    GROUP BY p.id
            ) t
            GROUP BY id
            ORDER BY remixes_count DESC '.
      $limit_clause.' '.
      $offset_clause.' ';
  }

  private function getAppendableSqlStringForEveryTerm(array $search_terms, array $metadata, bool $debug_build): string
  {
    $sql = '';
    $metadata_count = count($metadata);

    $debug_where = (!$debug_build) ? 'e.debug_build = false AND' : '';

    $search_terms_count = count($search_terms);
    for ($parameter_index = 0; $parameter_index < $search_terms_count; ++$parameter_index)
    {
      $parameter = ':st'.$parameter_index;
      $tag_string = '';
      $metadata_index = 0;
      foreach ($metadata as $language)
      {
        if ($metadata_index === $metadata_count - 1)
        {
          $tag_string .= '(t.'.$language.' LIKE '.$parameter.')';
        }
        else
        {
          $tag_string .= '(t.'.$language.' LIKE '.$parameter.') OR ';
        }
        ++$metadata_index;
      }

      $sql .= 'AND 
          ((e.name LIKE '.$parameter.' OR
          f.username LIKE '.$parameter.' OR
          e.description LIKE '.$parameter.' OR
          x.name LIKE '.$parameter.' OR
          '.$tag_string.' OR
          e.id = '.$parameter.'int'.') AND
          e.visible = true AND '.
        $debug_where.'
          e.private = false) ';
    }

    return $sql;
  }

  private function addFlavorCondition(QueryBuilder $query_builder, ?string $flavor, string $alias = 'e'): QueryBuilder
  {
    if ($flavor)
    {
      if ('!' === $flavor[0])
      {
        $query_builder
          ->andWhere($query_builder->expr()->neq($alias.'.flavor', ':flavor'))
          ->setParameter('flavor', substr($flavor, 1))
        ;
      }
      else
      {
        $query_builder
          ->andWhere($query_builder->expr()->eq($alias.'.flavor', ':flavor'))
          ->setParameter('flavor', $flavor)
        ;
      }
    }

    return $query_builder;
  }

  private function addDebugBuildCondition(QueryBuilder $query_builder, bool $debug_build, string $alias = 'e'): QueryBuilder
  {
    if (!$debug_build)
    {
      $query_builder->andWhere($query_builder->expr()->eq($alias.'.debug_build',
        $query_builder->expr()->literal(false)));
    }

    return $query_builder;
  }

  private function addMaxVersionCondition(QueryBuilder $query_builder, string $max_version = '0', string $alias = 'e'): QueryBuilder
  {
    if ('0' !== $max_version)
    {
      $query_builder
        ->andWhere($query_builder->expr()->lte($alias.'.language_version', ':max_version'))
        ->setParameter('max_version', $max_version)
      ;
    }

    return $query_builder;
  }

  private function addPrivacyCheckCondition(QueryBuilder $query_builder, string $alias = 'e'): QueryBuilder
  {
    $query_builder->andWhere(
      $query_builder->expr()->eq($alias.'.private', $query_builder->expr()->literal(false))
    );

    return $query_builder;
  }
}
