<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\Studios;

use App\DB\Entity\Studio\Studio;
use App\DB\Entity\Studio\StudioInvite;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<StudioInvite>
 */
class StudioInviteRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, StudioInvite::class);
  }

  /**
   * @return StudioInvite[]
   */
  public function findByStudio(Studio $studio): array
  {
    return $this->findBy(['studio' => $studio], ['created_on' => 'DESC']);
  }

  public function findOneByCode(string $code): ?StudioInvite
  {
    return $this->findOneBy(['code' => $code]);
  }

  /**
   * Counts one use, atomically: two pupils redeeming the last use at once must not both get in.
   *
   * @return bool false if the invite was used up or expired in the meantime
   */
  public function consumeUse(StudioInvite $invite): bool
  {
    $affected = $this->createQueryBuilder('i')
      ->update()
      ->set('i.uses', 'i.uses + 1')
      ->where('i.id = :id')
      ->andWhere('i.max_uses IS NULL OR i.uses < i.max_uses')
      ->andWhere('i.expires_at IS NULL OR i.expires_at > :now')
      ->setParameter('id', $invite->getId())
      ->setParameter('now', new \DateTime())
      ->getQuery()
      ->execute()
    ;

    return 1 === $affected;
  }
}
