<?php

namespace App\Repository;

use App\Entity\Program;
use App\Entity\ProgramDownloads;
use App\Entity\ProgramLike;
use App\Entity\ScratchProgramRemixRelation;
use App\Entity\Tag;
use ArrayIterator;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\Parameter;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;

class ProgramRepository extends ServiceEntityRepository
{
  private array $cached_most_remixed_programs_full_result = [];

  private array $cached_most_liked_programs_full_result = [];

  private array $cached_most_downloaded_other_programs_full_result = [];

  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Program::class);
  }

  /**
   * @return Program[]
   */
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

  /**
   * @return Program[]
   */
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

  /**
   * @return Program[]
   */
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
    bool $debug_build, string $flavor, Program $program, ?int $limit, int $offset,
    bool $is_test_environment): array
  {
    $cache_key = $flavor.'_'.$program->getId();
    if (isset($this->cached_most_downloaded_other_programs_full_result[$cache_key]))
    {
      return array_slice($this->cached_most_downloaded_other_programs_full_result[$cache_key],
        $offset, $limit);
    }

    $time_frame_length = 600; // in seconds
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

    if (!$is_test_environment)
    {
      $query_builder->andWhere($query_builder->expr()->between("TIME_DIFF(d1.downloaded_at, \n      d2.downloaded_at, 'second')",
        $query_builder->expr()->literal($time_frame_length / 2 * (-1)),
        $query_builder->expr()->literal($time_frame_length / 2)));
    }

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
    bool $debug_build, string $flavor, Program $program, bool $is_test_environment): int
  {
    $cache_key = $flavor.'_'.$program->getId();
    if (isset($this->cached_most_downloaded_other_programs_full_result[$cache_key]))
    {
      return count($this->cached_most_downloaded_other_programs_full_result[$cache_key]);
    }

    $result = $this->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
      $debug_build, $flavor, $program, 0, 0, $is_test_environment
    );
    $this->cached_most_downloaded_other_programs_full_result[$cache_key] = $result;

    return is_countable($this->cached_most_downloaded_other_programs_full_result[$cache_key]) ? count($this->cached_most_downloaded_other_programs_full_result[$cache_key]) : 0;
  }

  /**
   * @return Program[]
   */
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

  /**
   * @return Program[]
   */
  public function getRandomPrograms(bool $debug_build, string $flavor = null, int $limit = 20, int $offset = 0,
                                    string $max_version = '0'): array
  {
    // Rand(), newid() and TABLESAMPLE() doesn't exist in the Native Query
    // therefore we have to do a workaround for random results
    if ($offset > 0 && isset($_SESSION['randomProgramIds']))
    {
      $array_program_ids = $_SESSION['randomProgramIds'];
    }
    else
    {
      $array_program_ids = $this->getVisibleProgramIds($flavor, $debug_build, $max_version);
      shuffle($array_program_ids);
      $_SESSION['randomProgramIds'] = $array_program_ids;
    }

    $array_programs = [];
    $max_element = ($offset + $limit) > (is_countable($array_program_ids) ? count($array_program_ids) : 0) ? count($array_program_ids) : $offset + $limit;
    $current_element = $offset;

    while ($current_element < $max_element)
    {
      $array_programs[] = $this->find($array_program_ids[$current_element]);
      ++$current_element;
    }

    return $array_programs;
  }

  /**
   * @return mixed
   */
  public function getVisibleProgramIds(string $flavor = null, bool $debug_build = false,
                                       string $max_version = '0')
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e.id')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
    ;

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  /**
   * @return Program[]
   */
  public function getUserPublicPrograms(string $user_id, bool $debug_build, string $max_version,
                                        int $limit, int $offset, string $flavor = null): array
  {
    if ('' === $user_id)
    {
      return [];
    }

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

    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
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

  /**
   * @throws Exception
   */
  public function search(string $query, bool $debug_build, ?int $limit = 10, int $offset = 0, string $max_version = '0'): array
  {
    $em = $this->getEntityManager();
    $metadata = $em->getClassMetadata(Tag::class)->getFieldNames();
    array_shift($metadata);

    $debug_where = (!$debug_build) ? 'e.debug_build = false AND' : '';

    $query_addition_for_tags = '';
    $metadata_index = 0;
    $metadata_count = count($metadata);

    foreach ($metadata as $language)
    {
      if ($metadata_index === $metadata_count - 1)
      {
        $query_addition_for_tags .= '(t.'.$language.' LIKE :searchterm)';
      }
      else
      {
        $query_addition_for_tags .= '(t.'.$language.' LIKE :searchterm) OR ';
      }
      ++$metadata_index;
    }

    $search_terms = explode(' ', $query);

    $appendable_sql_string = '';
    $more_than_one_search_term = false;

    if (count($search_terms) > 1)
    {
      $appendable_sql_string = $this->getAppendableSqlStringForEveryTerm($search_terms,
        $metadata, $debug_build);
      $more_than_one_search_term = true;
    }

    $dql = 'SELECT e,
          (CASE
            WHEN (e.name LIKE :searchterm) THEN 10
            ELSE 0
          END) +
          (CASE
            WHEN (f.username LIKE :searchterm) THEN 1
            ELSE 0
          END) +
          (CASE
            WHEN (x.name LIKE :searchterm) THEN 7
            ELSE 0
          END) +
          (CASE
            WHEN (e.description LIKE :searchterm) THEN 3
            ELSE 0
          END) +
          (CASE
            WHEN (e.id = :searchtermint) THEN 11
            ELSE 0
          END) +
          (CASE
            WHEN ('.$query_addition_for_tags.') THEN 7
            ELSE 0
          END)
          AS weight
        FROM App\Entity\Program e
        LEFT JOIN e.user f
        LEFT JOIN e.tags t
        LEFT JOIN e.extensions x
        WHERE
          ((e.name LIKE :searchterm OR
          f.username LIKE :searchterm OR
          e.description LIKE :searchterm OR
          x.name LIKE :searchterm OR '.
      $query_addition_for_tags.' OR
          e.id = :searchtermint) AND
          e.visible = true AND '.
      $debug_where.'
          e.private = false) '.$appendable_sql_string;

    if ('0' !== $max_version)
    {
      $dql .= ' AND e.language_version <= '.$max_version;
    }

    $dql .= ' ORDER BY weight DESC, e.uploaded_at DESC';

    $qb_program = $this->createQueryBuilder('e');
    $final_query = $qb_program->getEntityManager()->createQuery($dql);
    $final_query->setFirstResult($offset);
    $final_query->setMaxResults($limit);
    $final_query->setParameter('searchterm', '%'.$query.'%');
    $final_query->setParameter('searchtermint', (int) $query);
    if ($more_than_one_search_term)
    {
      $parameter_index = 0;
      foreach ($search_terms as $search_term)
      {
        $parameter = ':st'.$parameter_index;
        ++$parameter_index;
        $final_query->setParameter($parameter, '%'.$search_term.'%');
        $final_query->setParameter($parameter.'int', (int) $search_term);
      }
    }

    $paginator = new Paginator($final_query);
    /** @var ArrayIterator $iterator */
    $iterator = $paginator->getIterator();
    $result = $iterator->getArrayCopy();

    return array_map(fn ($element) => $element[0], $result);
  }

  public function searchCount(string $query, bool $debug_build, string $max_version = '0'): int
  {
    $em = $this->getEntityManager();
    $metadata = $em->getClassMetadata(Tag::class)->getFieldNames();
    array_shift($metadata);

    $debug_where = (!$debug_build) ? 'e.debug_build = false AND' : '';

    $query_addition_for_tags = '';
    foreach ($metadata as $language)
    {
      $query_addition_for_tags .= 't.'.$language.' LIKE :searchterm OR ';
    }

    $search_terms = explode(' ', $query);
    $appendable_sql_string = '';
    $more_than_one_search_term = false;
    if (count($search_terms) > 1)
    {
      $appendable_sql_string = $this->getAppendableSqlStringForEveryTerm($search_terms,
        $metadata, $debug_build);
      $more_than_one_search_term = true;
    }

    $qb_program = $this->createQueryBuilder('e');
    $dql = 'SELECT e.id
        FROM App\Entity\Program e
        LEFT JOIN e.user f
        LEFT JOIN e.tags t
        LEFT JOIN e.extensions x
        WHERE
          ((e.name LIKE :searchterm OR
          f.username LIKE :searchterm OR
          e.description LIKE :searchterm OR
          x.name LIKE :searchterm OR '.
      $query_addition_for_tags.' 
          e.id = :searchtermint) AND
          e.visible = true AND '.
      $debug_where.'
          e.private = false) '.$appendable_sql_string;

    if ('0' !== $max_version)
    {
      $dql .= ' AND e.language_version <= '.$max_version;
    }
    $dql .= ' GROUP BY e.id';
    $db_query = $qb_program->getEntityManager()->createQuery($dql);
    $db_query->setParameter('searchterm', '%'.$query.'%');
    $db_query->setParameter('searchtermint', (int) $query);
    if ($more_than_one_search_term)
    {
      $parameter_index = 0;
      foreach ($search_terms as $search_term)
      {
        $parameter = ':st'.$parameter_index;
        ++$parameter_index;
        $db_query->setParameter($parameter, '%'.$search_term.'%');
        $db_query->setParameter($parameter.'int', (int) $search_term);
      }
    }
    $result = $db_query->getResult();

    return is_countable($result) ? count($result) : 0;
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

  public function getAuthUserPrograms(?string $username, ?int $limit, int $offset, ?string $flavor, bool $debug_build,
                                      string $max_version): array
  {
    if (null === $username)
    {
      return [];
    }

    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.user', 'f')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('f.username', ':username'))
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

  public function getPublicUserPrograms(?string $user_id, bool $debug_build, string $max_version = '0'): array
  {
    if (null === $user_id)
    {
      return [];
    }

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
      WHERE t.id IN (
        :tag_ids
      )
      OR x.id IN (
        :extension_ids
      )
      AND e.flavor = :flavor
      AND e.id != :id
      AND e.private = false
      AND e.visible = true '.
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

    return is_countable($db_query->getResult()) ? count($db_query->getResult()) : 0;
  }

  public function getRecommendedProgramsById(string $id, bool $debug_build, string $flavor, ?int $limit, int $offset = 0): array
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
      WHERE (t.id IN (
        :tag_ids
      )
      OR x.id IN (
        :extension_ids
      ))
      AND e.flavor = :flavor
      AND e.id != :pid
      AND e.private = false
      AND e.visible = TRUE '.
      $debug_where.'  
      GROUP BY e.id
      ORDER BY cnt DESC';

    $qb_program = $this->createQueryBuilder('e');
    $db_query = $qb_program->getEntityManager()->createQuery($dql);

    $parameters = new ArrayCollection();
    $parameters->add(new Parameter('pid', $id));
    $parameters->add(new Parameter('tag_ids', $tag_ids));
    $parameters->add(new Parameter('extension_ids', $extensions_id));
    $parameters->add(new Parameter('flavor', $flavor));

    $db_query->setParameters($parameters);

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
