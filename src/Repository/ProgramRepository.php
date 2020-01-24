<?php

namespace App\Repository;

use App\Entity\Program;
use Doctrine\DBAL\DBALException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;
use Exception;


/**
 * Class ProgramRepository
 * @package App\Repository
 */
class ProgramRepository extends ServiceEntityRepository
{
  /**
   * @param ManagerRegistry $managerRegistry
   */
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, Program::class);
  }

  /**
   * @var array
   */
  private $cached_most_remixed_programs_full_result = [];
  /**
   * @var array
   */
  private $cached_most_liked_programs_full_result = [];
  /**
   * @var array
   */
  private $cached_most_downloaded_other_programs_full_result = [];

  /**
   * @param bool        $debug_build If debug builds should be included
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   * @param string      $max_version
   *
   * @return Program[]
   */
  public function getMostDownloadedPrograms(bool $debug_build, $flavor = null, $limit = 20, $offset = 0,
                                            string $max_version = "0")
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder->select('e')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('e.downloads', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit);

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  /**
   * @param bool        $debug_build If debug builds should be included
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   * @param string      $max_version
   *
   * @return Program[]
   */
  public function getMostViewedPrograms(bool $debug_build, $flavor = null, $limit = 20, $offset = 0, string $max_version = "0")
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('e.views', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit);

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  /**
   * @param bool     $debug_build If debug builds should be included
   * @param int|null $limit
   * @param int      $offset
   *
   * @return string
   */
  private function generateUnionSqlStatementForMostRemixedPrograms(bool $debug_build,
                                                                   $limit, int $offset = 0)
  {
    //---------------------------------------------------------------------------------------------
    // ATTENTION: since Doctrine does not support UNION queries,
    //            the following query is a native MySQL/SQLite query
    //---------------------------------------------------------------------------------------------
    $limit_clause = intval($limit) > 0 ? 'LIMIT ' . intval($limit) : '';
    $offset_clause = intval($offset) > 0 ? 'OFFSET ' . intval($offset) : '';
    $debug_where = ($debug_build !== true) ? 'AND p.debug_build = false' : '';

    return '
            SELECT sum(remixes_count) AS total_remixes_count, id FROM (
                    SELECT p.id AS id, COUNT(p.id) AS remixes_count
                    FROM program p
                    INNER JOIN program_remix_relation r
                    ON p.id = r.ancestor_id
                    INNER JOIN program rp
                    ON r.descendant_id = rp.id
                    WHERE p.visible = 1 ' .
      $debug_where . ' 
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
                    WHERE p.visible = 1 ' .
      $debug_where . ' 
                    AND p.flavor = :flavor
                    AND p.private = 0
                    AND p.user_id <> rp.user_id
                    GROUP BY p.id
            ) t
            GROUP BY id
            ORDER BY remixes_count DESC ' .
      $limit_clause . ' ' .
      $offset_clause . ' ';
  }


  /**
   * @param bool     $debug_build If debug builds should be included
   * @param string   $flavor
   * @param int|null $limit
   * @param int      $offset
   *
   * @return array
   * @throws DBALException
   */
  public function getMostRemixedPrograms(bool $debug_build, $flavor = 'pocketcode', $limit = 20, $offset = 0)
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
   * @param bool   $debug_build If debug builds should be included
   * @param string $flavor
   *
   * @return int
   * @throws DBALException
   */
  public function getTotalRemixedProgramsCount(bool $debug_build, $flavor = 'pocketcode')
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

    return count($this->cached_most_remixed_programs_full_result[$flavor]);
  }

  /**
   * @param bool     $debug_build If debug builds should be included
   * @param string   $flavor
   * @param int|null $limit
   * @param int      $offset
   *
   * @return Program[]
   */
  public function getMostLikedPrograms(bool $debug_build, $flavor = 'pocketcode', $limit = 20, $offset = 0)
  {
    if (isset($this->cached_most_liked_programs_full_result[$flavor]))
    {
      return array_slice($this->cached_most_liked_programs_full_result[$flavor], $offset, $limit);
    }

    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select(['e as program', 'COUNT(e.id) as like_count'])
      ->innerJoin('App\Entity\ProgramLike', 'l', Join::WITH,
        $query_builder->expr()->eq('e.id', 'l.program_id'))
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->having($query_builder->expr()->gt('like_count', $query_builder->expr()->literal(1)))
      ->groupBy('e.id')
      ->orderBy('like_count', 'DESC')
      ->distinct();

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    if (intval($offset) > 0)
    {
      $query_builder->setFirstResult($offset);
    }

    if (intval($limit) > 0)
    {
      $query_builder->setMaxResults($limit);
    }

    $results = $query_builder->getQuery()->getResult();

    return array_map(function ($result) {
      return $result['program'];
    }, $results);
  }

  /**
   * @param bool   $debug_build If debug builds should be included
   * @param string $flavor
   *
   * @return int
   */
  public function getTotalLikedProgramsCount(bool $debug_build, $flavor = 'pocketcode')
  {
    if (isset($this->cached_most_liked_programs_full_result[$flavor]))
    {
      return count($this->cached_most_liked_programs_full_result[$flavor]);
    }

    $this->cached_most_liked_programs_full_result[$flavor] =
      $this->getMostLikedPrograms($debug_build, $flavor, 0, 0);

    return count($this->cached_most_liked_programs_full_result[$flavor]);
  }

  /**
   * @param bool     $debug_build If debug builds should be included
   * @param string   $flavor
   * @param Program  $program
   * @param int|null $limit
   * @param int      $offset
   * @param bool     $is_test_environment
   *
   * @return array
   */
  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
    bool $debug_build, string $flavor, Program $program, $limit, int $offset,
    bool $is_test_environment
  )
  {
    $cache_key = $flavor . '_' . $program->getId();
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
        'App\Entity\ProgramDownloads', 'd1',
        Join::WITH, $query_builder->expr()->eq('e.id', 'd1.program')
      )
      ->innerJoin(
        'App\Entity\ProgramDownloads', 'd2',
        Join::WITH, $query_builder->expr()->eq('d1.user', 'd2.user')
      )
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->isNotNull('d1.user'))
      ->andWhere($query_builder->expr()->neq('d1.user', ':user'))
      ->andWhere($query_builder->expr()->neq('d1.program', 'd2.program'))
      ->andWhere($query_builder->expr()->eq('d2.program', ':program'));

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    if (!$is_test_environment)
    {
      $query_builder->andWhere($query_builder->expr()->between('TIME_DIFF(d1.downloaded_at, 
      d2.downloaded_at, \'second\')',
        $query_builder->expr()->literal($time_frame_length / 2 * (-1)),
        $query_builder->expr()->literal($time_frame_length / 2)));
    }

    $query_builder
      ->groupBy('e.id')
      ->orderBy('user_download_count', 'DESC')
      ->setParameter('user', $program->getUser())
      ->setParameter('program', $program)
      ->distinct();

    if (intval($offset) > 0)
    {
      $query_builder->setFirstResult($offset);
    }

    if (intval($limit) > 0)
    {
      $query_builder->setMaxResults($limit);
    }

    $results = $query_builder->getQuery()->getResult();

    return array_map(function ($result) {
      return $result['program'];
    }, $results);
  }

  /**
   * @param bool    $debug_build If debug builds should be included
   * @param string  $flavor
   * @param Program $program
   * @param bool    $is_test_environment
   *
   * @return int
   */
  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgramCount(
    bool $debug_build, string $flavor, Program $program, bool $is_test_environment
  )
  {
    $cache_key = $flavor . '_' . $program->getId();
    if (isset($this->cached_most_downloaded_other_programs_full_result[$cache_key]))
    {
      return count($this->cached_most_downloaded_other_programs_full_result[$cache_key]);
    }

    $result = $this->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
      $debug_build, $flavor, $program, 0, 0, $is_test_environment
    );
    $this->cached_most_downloaded_other_programs_full_result[$cache_key] = $result;

    return count($this->cached_most_downloaded_other_programs_full_result[$cache_key]);
  }

  /**
   * @param bool        $debug_build If debug builds should be included
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   * @param string      $max_version
   *
   * @return Program[]
   */
  public function getRecentPrograms(bool $debug_build, $flavor = null, $limit = 20, $offset = 0, string $max_version = "0")
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)))
      ->orderBy('e.uploaded_at', 'DESC')
      ->setFirstResult($offset)
      ->setMaxResults($limit);

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  /**
   * @param bool        $debug_build If debug builds should be included
   * @param string|null $flavor
   * @param int|null    $limit
   * @param int         $offset
   * @param string      $max_version
   *
   * @return array
   */
  public function getRandomPrograms(bool $debug_build, $flavor = null, $limit = 20, $offset = 0, string $max_version = "0")
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
    $max_element = ($offset + $limit) > count($array_program_ids) ? count($array_program_ids) : $offset + $limit;
    $current_element = $offset;

    while ($current_element < $max_element)
    {
      $array_programs[] = $this->find($array_program_ids[$current_element]);
      $current_element++;
    }

    return $array_programs;
  }

  /**
   * @param string|null $flavor
   * @param bool        $debug_build If debug builds should be included
   * @param string      $max_version
   *
   * @return mixed
   */
  public function getVisibleProgramIds($flavor, bool $debug_build, string $max_version = "0")
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e.id')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)));

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  /**
   * @param array     $programs
   * @param bool      $debug_build If debug builds should be included
   * @param string    $max_version
   *
   * @return Program[]|array
   */
  public static function filterVisiblePrograms(array $programs, bool $debug_build, string $max_version = "0")
  {
    if (!is_array($programs) || count($programs) === 0)
    {
      return [];
    }

    /** @var Program[] $filtered_programs */
    $filtered_programs = [];

    foreach ($programs as $program)
    {
      if ($program->getVisible() === true && $program->getPrivate() === false &&
        ($debug_build === true || $program->isDebugBuild() === false) &&
        ($max_version === "0" || $max_version <= $program->getLanguageVersion()))
      {
        $filtered_programs[] = $program;
      }
    }

    return $filtered_programs;
  }

  /**
   * @param int[] $program_ids
   *
   * @return int[]
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function filterExistingProgramIds(array $program_ids)
  {
    $query_builder = $this->createQueryBuilder('p');

    $result = $query_builder
      ->select(['p.id'])
      ->where('p.id IN (:program_ids)')
      ->setParameter('program_ids', $program_ids)
      ->distinct()
      ->getQuery()
      ->getResult();

    return array_map(function ($data) {
      return $data['id'];
    }, $result);
  }

  /**
   * @param      $search_terms
   * @param      $metadata
   * @param bool $debug_build If debug builds should be included
   *
   * @return string
   */
  private function getAppendableSqlStringForEveryTerm($search_terms, $metadata, bool $debug_build)
  {
    $sql = '';
    $metadata_count = count($metadata);

    $debug_where = ($debug_build !== true) ? 'e.debug_build = false AND' : '';

    $search_terms_count = count($search_terms);
    for ($parameter_index = 0; $parameter_index < $search_terms_count; $parameter_index++)
    {
      $parameter = ':st' . $parameter_index;
      $tag_string = '';
      $metadata_index = 0;
      foreach ($metadata as $language)
      {
        if ($metadata_index === $metadata_count - 1)
        {
          $tag_string .= '(t.' . $language . ' LIKE ' . $parameter . ')';
        }
        else
        {
          $tag_string .= '(t.' . $language . ' LIKE ' . $parameter . ') OR ';
        }
        $metadata_index++;
      }


      $sql .= 'AND 
          ((e.name LIKE ' . $parameter . ' OR
          f.username LIKE ' . $parameter . ' OR
          e.description LIKE ' . $parameter . ' OR
          x.name LIKE ' . $parameter . ' OR
          ' . $tag_string . ' OR
          e.id = ' . $parameter . 'int' . ') AND
          e.visible = true AND ' .
        $debug_where . '
          e.private = false) ';
    }

    return $sql;
  }

  /**
   * @param string $query       The query to search for (search terms)
   * @param bool   $debug_build If debug builds should be included
   * @param int    $limit
   * @param int    $offset
   * @param string $max_version
   *
   * @return array
   * @throws Exception
   */
  public function search(string $query, bool $debug_build, $limit = 10, $offset = 0, string $max_version = "0")
  {
    $em = $this->getEntityManager();
    $metadata = $em->getClassMetadata('App\Entity\Tag')->getFieldNames();
    array_shift($metadata);

    $debug_where = ($debug_build !== true) ? 'e.debug_build = false AND' : '';

    $query_addition_for_tags = '';
    $metadata_index = 0;
    $metadata_count = count($metadata);

    foreach ($metadata as $language)
    {
      if ($metadata_index === $metadata_count - 1)
      {
        $query_addition_for_tags .= '(t.' . $language . ' LIKE :searchterm)';
      }
      else
      {
        $query_addition_for_tags .= '(t.' . $language . ' LIKE :searchterm) OR ';
      }
      $metadata_index++;
    }

    $search_terms = explode(" ", $query);

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
            WHEN (' . $query_addition_for_tags . ') THEN 7
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
          x.name LIKE :searchterm OR ' .
      $query_addition_for_tags . ' OR
          e.id = :searchtermint) AND
          e.visible = true AND ' .
      $debug_where . '
          e.private = false) ' . $appendable_sql_string;

    if ($max_version !== "0")
    {
      $dql .= " AND e.language_version <= " . $max_version;
    }

    $dql .= " ORDER BY weight DESC, e.uploaded_at DESC";

    $qb_program = $this->createQueryBuilder('e');
    $final_query = $qb_program->getEntityManager()->createQuery($dql);
    $final_query->setFirstResult($offset);
    $final_query->setMaxResults($limit);
    $final_query->setParameter('searchterm', '%' . $query . '%');
    $final_query->setParameter('searchtermint', intval($query));
    if ($more_than_one_search_term)
    {
      $parameter_index = 0;
      foreach ($search_terms as $search_term)
      {
        $parameter = ":st" . $parameter_index;
        $parameter_index++;
        $final_query->setParameter($parameter, '%' . $search_term . '%');
        $final_query->setParameter($parameter . 'int', intval($search_term));
      }
    }

    $paginator = new Paginator($final_query);
    $result = $paginator->getIterator()->getArrayCopy();

    return array_map(function ($element) {
      return $element[0];
    }, $result);
  }

  /**
   * @param string $query       The query to search for (search terms)
   * @param bool   $debug_build If debug builds should be included
   * @param string $max_version
   *
   * @return int
   */
  public function searchCount(string $query, bool $debug_build, string $max_version = "0")
  {
    $em = $this->getEntityManager();
    $metadata = $em->getClassMetadata('App\Entity\Tag')->getFieldNames();
    array_shift($metadata);

    $debug_where = ($debug_build !== true) ? 'e.debug_build = false AND' : '';

    $query_addition_for_tags = '';
    foreach ($metadata as $language)
    {
      $query_addition_for_tags .= 't.' . $language . ' LIKE :searchterm OR ';
    }

    $search_terms = explode(" ", $query);
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
          x.name LIKE :searchterm OR ' .
      $query_addition_for_tags . ' 
          e.id = :searchtermint) AND
          e.visible = true AND ' .
      $debug_where . '
          e.private = false) ' . $appendable_sql_string;

    if ($max_version !== "0")
    {
      $dql .= " AND e.language_version <= " . $max_version;
    }
    $dql .= " GROUP BY e.id";
    $db_query = $qb_program->getEntityManager()->createQuery($dql);
    $db_query->setParameter('searchterm', '%' . $query . '%');
    $db_query->setParameter('searchtermint', intval($query));
    if ($more_than_one_search_term)
    {
      $parameter_index = 0;
      foreach ($search_terms as $search_term)
      {
        $parameter = ":st" . $parameter_index;
        $parameter_index++;
        $db_query->setParameter($parameter, '%' . $search_term . '%');
        $db_query->setParameter($parameter . 'int', intval($search_term));
      }
    }
    $result = $db_query->getResult();

    return count($result);
  }

  /**
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function markAllProgramsAsNotYetMigrated()
  {
    $query_builder = $this->createQueryBuilder('p');

    $query_builder
      ->update()
      ->set('p.remix_migrated_at', ':remix_migrated_at')
      ->setParameter(':remix_migrated_at', null)
      ->getQuery()
      ->execute();
  }

  /**
   * @param int $previous_program_id
   *
   * @return mixed
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function findNext(int $previous_program_id)
  {
    $query_builder = $this->createQueryBuilder('p');

    return $query_builder
      ->select('min(p.id)')
      ->where($query_builder->expr()->gt('p.id', ':previous_program_id'))
      ->setParameter('previous_program_id', $previous_program_id)
      ->distinct()
      ->getQuery()
      ->getSingleScalarResult();
  }

  /**
   * @param        $user_id
   * @param bool   $debug_build If debug builds should be included
   * @param string $max_version
   *
   * @return Program[]
   */
  public function getUserPrograms($user_id, bool $debug_build, $max_version)
  {
    if (!$user_id)
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
      ->orderBy('e.uploaded_at', 'DESC');

    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  /**
   * @param        $user_id
   * @param bool   $debug_build If debug builds should be included
   * @param string $max_version
   *
   * @return Program[]
   */
  public function getPublicUserPrograms($user_id, bool $debug_build, string $max_version = "0")
  {
    if (!$user_id)
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
      ->orderBy('e.uploaded_at', 'DESC');

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return $query_builder->getQuery()->getResult();
  }

  /**
   * @param bool   $debug_build If debug builds should be included
   * @param null   $flavor
   * @param string $max_version
   *
   * @return int
   * @throws NonUniqueResultException
   * @throws \Doctrine\ORM\NoResultException
   */
  public function getTotalPrograms(bool $debug_build, $flavor = null, string $max_version = "0")
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('COUNT (e.id)')
      ->where($query_builder->expr()->eq('e.visible', $query_builder->expr()->literal(true)));

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);
    $query_builder = $this->addFlavorCondition($query_builder, $flavor);
    $query_builder = $this->addMaxVersionCondition($query_builder, $max_version);

    return (int)$query_builder->getQuery()->getSingleScalarResult();
  }

  /**
   * @param $apk_status
   *
   * @return mixed
   *
   * @internal
   * ATTENTION! Internal use only! (no visible/private/debug check)
   */
  public function getProgramsWithApkStatus($apk_status)
  {
    $query_builder = $this->createQueryBuilder('e');

    return $query_builder
      ->select('e')
      ->where($query_builder->expr()->eq('e.apk_status', ':apk_status'))
      ->setParameter('apk_status', $apk_status)
      ->getQuery()
      ->getResult();
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
      ->getResult();
  }

  /**
   * @param          $id
   * @param bool     $debug_build If debug builds should be included
   * @param int|null $limit
   * @param int      $offset
   *
   * @return Program[]
   */
  public function getProgramsByTagId($id, bool $debug_build, $limit = 20, $offset = 0)
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
      ->setMaxResults($limit);

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    return $query_builder
      ->getQuery()
      ->getResult();
  }

  /**
   * @param array $program_ids
   * @param bool  $debug_build If debug builds should be included
   *
   * @return Program[]
   */
  public function getProgramDataByIds(Array $program_ids, bool $debug_build)
  {
    $query_builder = $this->createQueryBuilder('p');

    $query_builder
      ->select(['p.id', 'p.name', 'p.uploaded_at', 'u.username'])
      ->innerJoin('p.user', 'u')
      ->where($query_builder->expr()->eq('p.visible',
        $query_builder->expr()->literal(true)))
      ->andWhere('p.id IN (:program_ids)')
      ->setParameter('program_ids', $program_ids)
      ->distinct();

    $query_builder = $this->addPrivacyCheckCondition($query_builder, 'p');
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build, 'p');

    return $query_builder
      ->getQuery()
      ->getResult();
  }

  /**
   * @param string   $name
   * @param bool     $debug_build If debug builds should be included
   * @param int|null $limit
   * @param int      $offset
   *
   * @return mixed
   */
  public function getProgramsByExtensionName(string $name, bool $debug_build,
                                             $limit = 20, $offset = 0)
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
      ->setMaxResults($limit);

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    return $query_builder
      ->getQuery()
      ->getResult();
  }

  /**
   * @param string $query       The query to search for (search terms)
   * @param bool   $debug_build If debug builds should be included
   *
   * @return int
   */
  public function searchTagCount(string $query, bool $debug_build)
  {
    $query = str_replace("yahoo", "", $query);

    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.tags', 't')
      ->where($query_builder->expr()->eq('e.visible',
        $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('t.id', ':id'))
      ->setParameter('id', $query);

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    $result = $query_builder
      ->getQuery()
      ->getResult();

    return count($result);
  }

  /**
   * @param string $query       The query to search for (search terms)
   * @param bool   $debug_build If debug builds should be included
   *
   * @return int
   */
  public function searchExtensionCount(string $query, bool $debug_build)
  {
    $query_builder = $this->createQueryBuilder('e');

    $query_builder
      ->select('e')
      ->leftJoin('e.extensions', 't')
      ->where($query_builder->expr()->eq('e.visible',
        $query_builder->expr()->literal(true)))
      ->andWhere($query_builder->expr()->eq('t.name', ':name'))
      ->setParameter('name', $query);

    $query_builder = $this->addPrivacyCheckCondition($query_builder);
    $query_builder = $this->addDebugBuildCondition($query_builder, $debug_build);

    $result = $query_builder
      ->getQuery()
      ->getResult();

    return count($result);
  }


  /**
   * @param        $id
   * @param bool   $debug_build If debug builds should be included
   * @param string $flavor
   *
   * @return int
   */
  public function getRecommendedProgramsCount($id, bool $debug_build, $flavor = 'pocketcode')
  {
    $qb_tags = $this->createQueryBuilder('e');

    $result = $qb_tags
      ->select('t.id')
      ->leftJoin('e.tags', 't')
      ->where($qb_tags->expr()->eq('e.id', ':id'))
      ->setParameter('id', $id)
      ->getQuery()
      ->getResult();

    $qb_extensions = $this->createQueryBuilder('e');

    $result_2 = $qb_extensions
      ->select('x.id')
      ->leftJoin('e.extensions', 'x')
      ->where($qb_tags->expr()->eq('e.id', ':id'))
      ->setParameter('id', $id)
      ->getQuery()
      ->getResult();

    $tag_ids = array_map('current', $result);
    $extensions_id = array_map('current', $result_2);

    $debug_where = ($debug_build !== true) ? 'AND e.debug_build = false' : '';

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
      AND e.visible = true ' .
      $debug_where . '
      GROUP BY e.id
      ORDER BY cnt DESC';

    $qb_program = $this->createQueryBuilder('e');
    $db_query = $qb_program->getEntityManager()->createQuery($dql);
    $db_query->setParameters([
      'id'            => $id,
      'tag_ids'       => $tag_ids,
      'extension_ids' => $extensions_id,
      'flavor'        => $flavor,
    ]);

    return count($db_query->getResult());
  }

  /**
   * @param          $id
   * @param bool     $debug_build If debug builds should be included
   * @param string   $flavor
   * @param int|null $limit
   * @param int      $offset
   *
   * @return array
   */
  public function getRecommendedProgramsById($id, bool $debug_build, string $flavor, $limit, int $offset = 0)
  {

    $qb_tags = $this->createQueryBuilder('e');

    $result = $qb_tags
      ->select('t.id')
      ->leftJoin('e.tags', 't')
      ->where($qb_tags->expr()->eq('e.id', ':id'))
      ->setParameter('id', $id)
      ->getQuery()
      ->getResult();

    $qb_extensions = $this->createQueryBuilder('e');

    $result_2 = $qb_extensions
      ->select('x.id')
      ->leftJoin('e.extensions', 'x')
      ->where($qb_tags->expr()->eq('e.id', ':id'))
      ->setParameter('id', $id)
      ->getQuery()
      ->getResult();

    $tag_ids = array_map('current', $result);
    $extensions_id = array_map('current', $result_2);

    $debug_where = ($debug_build !== true) ? 'AND e.debug_build = false' : '';

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
      AND e.visible = TRUE ' .
      $debug_where . '  
      GROUP BY e.id
      ORDER BY cnt DESC';

    $qb_program = $this->createQueryBuilder('e');
    $db_query = $qb_program->getEntityManager()->createQuery($dql);
    $db_query->setParameters([
      'pid'           => $id,
      'tag_ids'       => $tag_ids,
      'extension_ids' => $extensions_id,
      'flavor'        => $flavor,
    ]);

    $db_query->setFirstResult($offset);
    $db_query->setMaxResults($limit);

    $id_list = array_map(function ($value) {
      return $value['id'];
    }, $db_query->getResult());

    $programs = [];
    foreach ($id_list as $id)
    {
      array_push($programs, $this->find($id));
    }

    return $programs;

  }

  /**
   * @param QueryBuilder $query_builder
   * @param string|null  $flavor
   * @param string       $alias The QueryBuilder alias to use
   *
   * @return QueryBuilder
   */
  private function addFlavorCondition(QueryBuilder $query_builder, $flavor, string $alias = 'e')
  {
    if ($flavor)
    {
      if ($flavor{0} === "!")
      {
        $query_builder
          ->andWhere($query_builder->expr()->neq($alias . '.flavor', ':flavor'))
          ->setParameter('flavor', substr($flavor, 1));
      }
      else
      {
        $query_builder
          ->andWhere($query_builder->expr()->eq($alias . '.flavor', ':flavor'))
          ->setParameter('flavor', $flavor);
      }
    }

    return $query_builder;
  }

  /**
   * @param QueryBuilder $query_builder
   * @param bool         $debug_build If debug builds should be included
   * @param string       $alias
   *
   * @return QueryBuilder
   */
  private function addDebugBuildCondition(QueryBuilder $query_builder, bool $debug_build, string $alias = 'e')
  {
    if ($debug_build !== true)
    {
      $query_builder->andWhere($query_builder->expr()->eq($alias . '.debug_build',
        $query_builder->expr()->literal(false)));
    }

    return $query_builder;
  }

  /**
   * @param QueryBuilder $query_builder
   * @param string       $max_version
   * @param string       $alias The QueryBuilder alias to use
   *
   * @return QueryBuilder
   */
  private function addMaxVersionCondition(QueryBuilder $query_builder, string $max_version = "0", string $alias = 'e')
  {
    if ($max_version !== "0")
    {
      $query_builder
        ->andWhere($query_builder->expr()->lte($alias . '.language_version', ':max_version'))
        ->setParameter('max_version', $max_version);
    }

    return $query_builder;
  }


  /**
   * @param QueryBuilder $query_builder
   * @param string       $alias The QueryBuilder alias to use
   *
   * @return QueryBuilder
   */
  private function addPrivacyCheckCondition(QueryBuilder $query_builder, string $alias = 'e')
  {
    $query_builder->andWhere(
      $query_builder->expr()->eq($alias . '.private', $query_builder->expr()->literal(false))
    );

    return $query_builder;
  }

}
