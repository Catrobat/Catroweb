<?php

namespace App\DB\EntityRepository\Project;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\DB\Entity\Project\Scratch\ScratchProgramRemixRelation;
use App\DB\Entity\User\User;
use App\Utils\RequestHelper;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Elastica\Query;
use Elastica\Query\BoolQuery;
use Elasticsearch\Client;
use Elasticsearch\ClientBuilder;
use Google\Type\DateTime;

class ProgramRepository extends ServiceEntityRepository
{
  private ?Client $elasticsearch_client = null;
  public function __construct(ManagerRegistry $managerRegistry, protected RequestHelper $app_request)
  {
    parent::__construct($managerRegistry, Program::class);
    $this->elasticsearch_client = ClientBuilder::create()->setHosts(['elasticsearch:9200'])->build();
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

  public function getProjects(string $flavor = null, string $max_version = '', int $limit = 20, int $offset = 0, string $order_by = '', string $order = 'DESC'): array
  {
    $query_builder = new BoolQuery();
    //$query_builder = $this->excludeUnavailableAndPrivateProjects($query_builder, 'embroidery', '0.982');
    $this->excludePrivateProjects($query_builder);

    $fields = $this->excludeUnavailableAndPrivateProjects();
    $params = [
      'index' => 'app_program',
      'body' => [
        'from' => $this->getOffset($offset),
        'size' => $this->getLimit($limit),
        'query' => $this->buildQuery($fields),
        'sort' => $this->setOrderBy($order_by, $order),
      ],
    ];
    $response = $this->elasticsearch_client->search($params);

    $hits = $response['hits']['hits'];
    $programs = [];
    foreach($hits as $hit)
    {
      $program_data = $hit['_source'];
      $program = new Program();
      $program->setId($program_data['id']);
      $program->setName($program_data['name']);
      $program->setUploadedAt(\DateTime::createFromFormat('Y-m-d\TH:i:sP', $program_data['uploaded_at']));
      $program->setDownloads($program_data['downloads']);
      $user = new User();
      $user->setUsername($program_data['getUsernameString']);
      $program->setUser($user);
      $programs[] = $program;
    }

    return $programs;
  }

  public function countProjects(string $flavor = null, string $max_version = ''): int
  {
    $query_builder = $this->createQueryCountBuilder();
    $query_builder = $this->excludeUnavailableAndPrivateProjects($query_builder, $flavor, $max_version);

    return $this->getQueryCount($query_builder);
  }

  public function getTrendingProjects(string $flavor = null, string $max_version = '', int $limit = 20, int $offset = 0, string $order_by = '', string $order = 'DESC'): array
  {
    $now = new \DateTime('now', new \DateTimeZone('UTC'));
    $interval = new \DateInterval('P7D');
    $query_builder = $this->createQueryAllBuilder();
    $query_builder = $this->excludeUnavailableAndPrivateProjects($query_builder, $flavor, $max_version);
    $query_builder = $this->setPagination($query_builder, $limit, $offset);
    $query_builder = $this->setOrderBy($query_builder, $order_by, $order);
    $date_time_delta_7_days = $query_builder->expr()->literal($now->sub($interval)->format('Y-m-d H:i:s'));
    $left_or = $query_builder->expr()->gte('e.uploaded_at', $date_time_delta_7_days);
    $right_or = $query_builder->expr()->gte('e.last_modified_at', $date_time_delta_7_days);
    $query_builder->andWhere($query_builder->expr()->orX($left_or, $right_or));

    return $query_builder->getQuery()->getResult();
  }

  public function getScratchRemixProjects(string $flavor = null, string $max_version = '', int $limit = 20, int $offset = 0): array
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

  public function countScratchRemixProjects(string $flavor = null, string $max_version = ''): int
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

  public function countPublicUserProjects(string $user_id, string $flavor = null, string $max_version = ''): int
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

  public function countUserProjectsIncludingPrivateOnes(string $user_id, string $flavor = null, string $max_version = ''): int
  {
    $qb = $this->createQueryCountBuilder();
    $qb = $this->excludeUnavailableProjects($qb, $flavor, $max_version);
    $qb
      ->andWhere($qb->expr()->eq('e.user', ':user_id'))
      ->setParameter('user_id', $user_id)
    ;

    return $this->getQueryCount($qb);
  }

  public function getProjectsByTagInternalTitle(string $internal_title, ?int $limit = 20, ?int $offset = 0, string $flavor = null, string $max_version = ''): array
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

  public function searchTagCount(string $tag_name, string $flavor = null, string $max_version = ''): int
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

  public function searchExtensionCount(string $extension_internal_title, string $flavor = null, string $max_version = ''): int
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

  public function getMoreProjectsFromUser(string $user_id, string $project_id, string $flavor = null, string $max_version = '', ?int $limit = 20, ?int $offset = 0): array
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

  public function getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(string $flavor, Program $program, ?int $limit, int $offset): array
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

  private function setOrderBy(string $order_by = '', string $order = 'DESC'): array
  {
    if ('' !== trim($order_by)) {
      return [$order_by => ['order' => $order]];
//      $query_builder = $query_builder
//        ->orderBy($alias.'.'.$order_by, $order)
//      ;
    }

    return [];
  }

  private function setPagination(?int $limit, ?int $offset): array
  {
    $return_val_offset = '';
    $return_val_limit = '';
    $offset = 2;
    if (null !== $offset && $offset >= 0) {
      $return_val_offset = ['from' => [$offset]];
      //$query_builder->setFirstResult($offset);
    }
    if (null !== $limit && $limit > 0) {
      $return_val_limit = ['size' => [$limit]];
      //$query_builder->setMaxResults($limit);
    }

    return $return_val_offset + $return_val_limit;//$query_builder;
  }


  private function excludeUnavailableAndPrivateProjects(BoolQuery $qb, string $flavor = null, string $max_version = ''): array
  {
    return $this->excludePrivateProjects($qb);
    //return $this->excludeUnavailableProjects($flavor, $max_version) + $this->excludePrivateProjects();
    //$qb = $this->excludeUnavailableProjects($qb, $flavor, $max_version, $alias);
  }

  private function excludeUnavailableProjects(string $flavor = null, string $max_version = ''): array
  {
    $return_value = array();
    $return_value[] = $this->excludeInvisibleProjects();
    if(($temp = $this->excludeDebugProjects()) != [])
      $return_value[] = $temp;
    if(($temp = $this->setFlavorConstraint($flavor)) != [])
      $return_value[] = $temp;
    if(($temp = $this->excludeProjectsWithTooHighLanguageVersion($max_version)) != [])
      $return_value[] = $temp;
    return $return_value;
//    $qb = $this->excludeInvisibleProjects($qb, $alias);
//    $qb = $this->excludeDebugProjects($qb, $alias);
//    $qb = $this->setFlavorConstraint($qb, $flavor, $alias);

    //return $this->excludeProjectsWithTooHighLanguageVersion($qb, $max_version, $alias);
  }

  private function setFlavorConstraint(string $flavor = null): array
  {
    if ('' === trim($flavor)) {
      return [];
      //return $query_builder;
    }

    if ('!' === $flavor[0]) {
      return ['term' => ['flavor' => substr($flavor,1)]];
      // Can be used when we explicitly want projects of other flavors (E.g to fill empty categories of a new flavor)
//      return $query_builder
//        ->andWhere($query_builder->expr()->neq($alias.'.flavor', ':flavor'))
//        ->setParameter('flavor', substr((string) $flavor, 1))
//      ;
    }


    return['bool' => [
          'should' => [
            [
              'match' => [
                'flavor' => strtolower($flavor),
              ],
            ],
            [
              'match' => [
                'getExtensionsString' => strtolower($flavor),
              ],
            ],
          ],
        ],
      ];

    // Extensions are very similar to Flavors. (E.g. it does not care if a project has embroidery flavor or extension)
//    return $query_builder->leftJoin($alias.'.extensions', 'ext')
//      ->andWhere($query_builder->expr()->orX()->addMultiple([
//        $query_builder->expr()->like('lower('.$alias.'.flavor)', ':flavor'),
//        $query_builder->expr()->like('lower(ext.internal_title)', ':extension'),
//      ]))
//      ->setParameter('flavor', strtolower((string) $flavor))
//      ->setParameter('extension', strtolower((string) $flavor))
//    ;
  }

  private function excludeProjectsWithTooHighLanguageVersion(string $max_version = ''): array
  {
    if ('' !== $max_version) {
      return ['term' => ['language_version' => $max_version]];
//      $query_builder
//        ->andWhere($query_builder->expr()->lte($alias.'.language_version', ':max_version'))
//        ->setParameter('max_version', $max_version)
//      ;
    }
    return [];
    //return $query_builder;
  }

  private function excludeDebugProjects(): array
  {
    if (!$this->app_request->isDebugBuildRequest() && 'dev' !== $_ENV['APP_ENV']) {
      return ['term' => ['debug_build' => false]];
    }
      return [];
  }

  private function excludeInvisibleProjects(BoolQuery $qb): void
  {
    $qb->addMust(new Query\Term(['visible' => true]));
  }

  private function excludePrivateProjects(BoolQuery $qb): void
  {
      $qb->addMust(new Query\Term(['private' => false]));
//    return [['term' => ['private' => false]]];
//    return $query_builder->andWhere(
//      $query_builder->expr()->eq($alias.'.private', $query_builder->expr()->literal(false))
//    );
  }

  private function getOffset(int $offset): int
  {
    return (null != $offset && $offset > 0) ? $offset: 0;
  }

  private function getLimit(int $limit): int
  {
    return (null != $limit && $limit > 0) ? $limit: 20;
  }

  private function buildQuery( ?array $filterFields): array
  {
//    return [
//      'bool' => [
//        'must' => $this->excludeUnavailableAndPrivateProjects($flavor, $max_version),
//      ],
//    ];

    $query = ['bool' => ['must' => []]];

    if ($filterFields) {
      foreach ($filterFields as $key => $field) {
        if (isset($filterValues[$key])) {
          $query['bool']['must'][] = ['term' => [$field => $filterValues[$key]]];
        }
      }
    }

    return $query;
  }
}
