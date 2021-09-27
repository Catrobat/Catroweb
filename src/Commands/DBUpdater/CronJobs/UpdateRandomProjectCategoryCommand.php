<?php

namespace App\Commands\DBUpdater\CronJobs;

use App\Entity\Program;
use App\Entity\ProgramManager;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class UpdateRandomProjectCategoryCommand extends Command
{
  protected static $defaultName = 'catrobat:workflow:update_random_project_category';

  protected const LIMIT = 100;
  protected EntityManagerInterface $entity_manager;
  protected ProgramManager $program_manager;

  public function __construct(EntityManagerInterface $entity_manager, ProgramManager $program_manager)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
    $this->program_manager = $program_manager;
  }

  protected function configure(): void
  {
    $this->setName(self::$defaultName)
      ->setDescription('Update random projects\' category.')
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $qb = $this->entity_manager->createQueryBuilder();
    $qb->update()
      ->from(Program::class, 'p')
      ->where('p.rand <> 0')
      ->set('p.rand', 0)
      ->getQuery()
      ->execute()
    ;

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
}
