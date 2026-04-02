<?php

declare(strict_types=1);

namespace App\DB\EntityRepository\System;

use App\DB\Entity\System\EmailDailyBudget;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<EmailDailyBudget>
 */
class EmailDailyBudgetRepository extends ServiceEntityRepository
{
  public function __construct(ManagerRegistry $managerRegistry)
  {
    parent::__construct($managerRegistry, EmailDailyBudget::class);
  }

  public function findOrCreateToday(): EmailDailyBudget
  {
    $today = new \DateTime('today');
    $budget = $this->findOneBy(['date' => $today]);

    if (!$budget instanceof EmailDailyBudget) {
      $budget = new EmailDailyBudget();
      $budget->setDate($today);
      $this->getEntityManager()->persist($budget);
      $this->getEntityManager()->flush();
    }

    return $budget;
  }
}
