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
    if (null === $flavor)
    {
      return $query_builder;
    }

    if ('!' === $flavor[0])
    {
      // Can be used when we explicitly want projects of other flavors (E.g to fill empty categories of a new flavor)
      return $query_builder
        ->andWhere($query_builder->expr()->neq($alias.'.flavor', ':flavor'))
        ->setParameter('flavor', substr($flavor, 1))
        ;
    }

    // Extensions are very similar to Flavors. (E.g. it does not care if a project has embroidery flavor or extension)
    return $query_builder->leftJoin($alias.'.extensions', 'ext')
      ->andWhere($query_builder->expr()->orX(
        $query_builder->expr()->like('lower('.$alias.'.flavor)', ':flavor'),
        $query_builder->expr()->like('lower(ext.name)', ':extension')
      ))
      ->setParameter('flavor', strtolower($flavor))
      ->setParameter('extension', strtolower($flavor))
      ;
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

  public static function addFeaturedExampleFlavorCondition(QueryBuilder $query_builder, ?string $flavor = null, string $alias = 'e', bool $include_pocketcode = false): QueryBuilder
  {
    if (null !== $flavor)
    {
      $where = 'fl.name = :name';
      if ($include_pocketcode)
      {
        $where .= ' OR fl.name = \'pocketcode\'';
      }
      $query_builder
        ->join($alias.'.flavor', 'fl')
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
