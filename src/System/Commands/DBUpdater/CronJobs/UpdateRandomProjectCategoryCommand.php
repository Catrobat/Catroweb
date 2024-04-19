<?php

declare(strict_types=1);

namespace App\System\Commands\DBUpdater\CronJobs;

use App\DB\Entity\Project\Program;
use App\Project\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'catrobat:workflow:update_random_project_category', description: 'Update random projects\' category.')]
class UpdateRandomProjectCategoryCommand extends Command
{
  protected const LIMIT = 100;

  public function __construct(protected EntityManagerInterface $entity_manager, protected ProjectManager $program_manager)
  {
    parent::__construct();
  }

  /**
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->resetCategoryOfIndex('rand');

    $qb = $this->entity_manager->createQueryBuilder();
    $random_id_list = $qb->select('p.id')
      ->from(Program::class, 'p')
      ->setMaxResults(self::LIMIT)
      ->orderBy('RAND()', 'DESC')
      ->getQuery()
      ->getArrayResult()
    ;

    $rand_value = 1;
    foreach ($random_id_list as $arr_project) {
      $id = $arr_project['id'];
      /** @var Program|null $program */
      $program = $this->program_manager->find($id);
      $program->setRand($rand_value);
      ++$rand_value;
      $this->entity_manager->persist($program);
    }
    $this->entity_manager->flush();

    return 0;
  }

  protected function resetCategoryOfIndex(string $index): void
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->update()
      ->from(Program::class, 'p')
      ->where("p.{$index} <> 0")
      ->set("p.{$index}", 0)
      ->getQuery()
      ->execute()
    ;
  }
}
