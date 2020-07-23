<?php

namespace App\Utils;

use App\Entity\Program;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

class APIQueryHelper
{
  public static function addMaxVersionCondition(QueryBuilder $query_builder, ?string $max_version = null, string $alias = 'e'): QueryBuilder
  {
    if (null !== $max_version && '0' !== $max_version)
    {
      $query_builder
        ->innerJoin(Program::class, 'p', Join::WITH,
          $query_builder->expr()->eq('e.program', 'p')->__toString())
        ->andWhere($query_builder->expr()->lte('p.language_version', ':max_version'))
        ->setParameter('max_version', $max_version)
        ->addOrderBy('e.id', 'ASC')
        ->addOrderBy('e.priority', 'DESC')
      ;
    }

    return $query_builder;
  }

  public static function addFlavorCondition(QueryBuilder $query_builder, ?string $flavor = null, string $alias = 'e'): QueryBuilder
  {
    if (null !== $flavor)
    {
      $query_builder
        ->andWhere($query_builder->expr()->eq($alias.'.flavor', ':flavor'))
        ->setParameter('flavor', $flavor)
      ;
    }

    return $query_builder;
  }

  public static function addFileFlavorsCondition(QueryBuilder $query_builder, ?string $flavor = null, string $alias = 'e', bool $include_pocketcode = false): QueryBuilder
  {
    if (null !== $flavor)
    {
      $where = 'fl.name = :name';
      if ($include_pocketcode)
      {
        $where .= ' OR fl.name = \'pocketcode\'';
      }
      $query_builder
        ->join($alias.'.flavors', 'fl')
        ->andWhere($where)
        ->setParameter('name', $flavor)
      ;
    }

    return $query_builder;
  }

  public static function addPlatformCondition(QueryBuilder $query_builder, ?string $platform = null): QueryBuilder
  {
    if (null !== $platform)
    {
      if ('android' === $platform)
      {
        $query_builder
          ->andWhere($query_builder->expr()->eq('e.for_ios', ':for_ios'))
          ->setParameter('for_ios', false)
        ;
      }
      else
      {
        $query_builder
          ->andWhere($query_builder->expr()->eq('e.for_ios', ':for_ios'))
          ->setParameter('for_ios', true)
        ;
      }
    }

    return $query_builder;
  }
}
