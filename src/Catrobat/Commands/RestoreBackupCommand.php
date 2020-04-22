<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use App\Entity\Program;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RestoreBackupCommand extends Command
{
  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    parent::__construct();
    $this->entity_manager = $entity_manager;
  }

  protected function configure()
  {
    $this->setName('catrobat:backup:restore')
      ->setDescription('Restores a borg backup')
        ;
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  protected function execute(InputInterface $input, OutputInterface $output): void
  {
    $output->writeln('Restore backup on localhost');

    CommandHelper::executeShellCommand(
            ['bin/console', 'catrobat:purge', '--force'], [], 'Purging database', $output
        );

    CommandHelper::executeShellCommand(
            ['sh', 'bin/borg_restore_share.sh'], [86400],
            'Executing borg restore script [timeout = 24h]', $output
        );

    $query = $this->entity_manager
      ->createQuery('UPDATE App\Entity\Program p SET p.apk_status = :status WHERE p.apk_status != :status')
        ;
    $query->setParameter('status', Program::APK_NONE);
    $result = $query->getSingleScalarResult();
    $output->writeln('Reset the apk status of '.$result.' projects');

    $query = $this->entity_manager
      ->createQuery('UPDATE App\Entity\Program p SET p.directory_hash = :hash WHERE p.directory_hash != :hash')
        ;
    $query->setParameter('hash', 'null');
    $result = $query->getSingleScalarResult();
    $output->writeln('Reset the directory hash of '.$result.' projects');

    $output->writeln('Import finished!');
  }
}
