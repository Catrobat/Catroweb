<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\MediaLibrary;

use App\DB\Entity\MediaLibrary\MediaCategory;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<MediaCategory>
 */
class MediaCategoryRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $manager_registry)
  {
    parent::__construct($manager_registry, MediaCategory::class);
  }

  /**
   * @return list<MediaCategory>
   */
  #[\Override]
  public function findAll(): array
  {
    return $this->findBy([], ['priority' => 'ASC', 'name' => 'ASC']);
  }

  /**
   * @return array<MediaCategory>
   */
  public function findPaginated(int $limit = 20, int $offset = 0): array
  {
    $qb = $this->createQueryBuilder('c')
      ->orderBy('c.priority', 'ASC')
      ->addOrderBy('c.name', 'ASC')
      ->setFirstResult($offset)
      ->setMaxResults($limit)
    ;

    return $qb->getQuery()->getResult();
  }

  /**
   * Keyset cursor query for categories ordered by priority ASC, id ASC.
   *
   * @return array<MediaCategory>
   */
  public function findPaginatedKeyset(int $limit, ?int $cursor_priority = null, ?string $cursor_id = null): array
  {
    $qb = $this->createQueryBuilder('c')
      ->orderBy('c.priority', 'ASC')
      ->addOrderBy('c.id', 'ASC')
      ->setMaxResults($limit)
    ;

    if (null !== $cursor_priority && null !== $cursor_id) {
      $qb->andWhere(
        '(c.priority > :cursor_priority) OR (c.priority = :cursor_priority AND c.id > :cursor_id)'
      )
        ->setParameter('cursor_priority', $cursor_priority)
        ->setParameter('cursor_id', $cursor_id)
      ;
    }

    return $qb->getQuery()->getResult();
  }

  public function countAll(): int
  {
    $qb = $this->createQueryBuilder('c')
      ->select('COUNT(c.id)')
    ;

    return (int) $qb->getQuery()->getSingleScalarResult();
  }

  public function createCategory(
    string $name,
    ?string $description,
    int $priority,
  ): MediaCategory {
    $category = new MediaCategory();
    $category->setName($name);
    $category->setDescription($description);
    $category->setPriority($priority);

    $this->getEntityManager()->persist($category);
    $this->getEntityManager()->flush();

    return $category;
  }

  public function save(MediaCategory $category): void
  {
    $this->getEntityManager()->persist($category);
    $this->getEntityManager()->flush();
  }

  public function delete(MediaCategory $category): void
  {
    $this->getEntityManager()->remove($category);
    $this->getEntityManager()->flush();
  }
}
