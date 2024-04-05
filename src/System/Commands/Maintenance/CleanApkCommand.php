<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\Project\Program;
use App\Storage\FileHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'catrobat:clean:apk', description: 'Delete the APKs and resets the status to NONE')]
class CleanApkCommand extends Command
{
  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $output1 = $output;

    $output1->writeln('Deleting APKs');
    $apk_dir = (string) $this->parameter_bag->get('catrobat.apk.dir');
    FileHelper::emptyDirectory($apk_dir);

    $query = $this->entity_manager
      ->createQuery('UPDATE App\DB\Entity\Project\Program p SET p.apk_status = :status WHERE p.apk_status != :status')
    ;
    $query->setParameter('status', Program::APK_NONE);
    $result = $query->getSingleScalarResult();
    $output1->writeln('Reset the apk status of '.$result.' projects');

    return 0;
  }
}
