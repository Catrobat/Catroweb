<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use App\Entity\Program;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class CleanApkCommand.
 */
class CleanApkCommand extends Command
{
  /**
   * @var
   */
  private $output;

  /**
   * @var EntityManagerInterface
   */
  private $entity_manager;

  /**
   * @var ParameterBagInterface
   */
  private $parameter_bag;

  /**
   * CleanApkCommand constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
    $this->entity_manager = $entity_manager;
  }

  protected function configure()
  {
    $this->setName('catrobat:clean:apk')
      ->setDescription('Delete the APKs and resets the status to NONE')
    ;
  }

  /**
   * @throws \Doctrine\ORM\NonUniqueResultException
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;

    $this->output->writeln('Deleting APKs');
    $apk_dir = $this->parameter_bag->get('catrobat.apk.dir');
    CommandHelper::emptyDirectory($apk_dir, 'Emptying apk directory', $output);

    $query = $this->entity_manager
      ->createQuery('UPDATE App\\Entity\\Program p SET p.apk_status = :status WHERE p.apk_status != :status')
    ;
    $query->setParameter('status', Program::APK_NONE);
    $result = $query->getSingleScalarResult();
    $this->output->writeln('Reset the apk status of '.$result.' projects');
  }
}
