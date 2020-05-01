<?php

namespace App\Commands\Maintenance;

use App\Commands\Helpers\CommandHelper;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CleanExtractedFileCommand extends Command
{
  protected static $defaultName = 'catrobat:clean:extracted';
  private OutputInterface $output;

  private EntityManagerInterface $entity_manager;

  private ParameterBagInterface $parameter_bag;

  public function __construct(EntityManagerInterface $entity_manager, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
    $this->entity_manager = $entity_manager;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:clean:extracted')
      ->setDescription('Delete the extracted programs and sets the directory hash to NULL')
    ;
  }

  /**
   * @throws NoResultException
   * @throws NonUniqueResultException
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $this->output = $output;

    $this->output->writeln('Deleting Extracted Catrobat Files');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.file.extract.dir'), 'Emptying extracted directory', $output);

    $query = $this->entity_manager
      ->createQuery('UPDATE App\Entity\Program p SET p.directory_hash = :hash WHERE p.directory_hash != :hash')
    ;
    $query->setParameter('hash', 'null');
    $result = $query->getSingleScalarResult();
    $this->output->writeln('Reset the directory hash of '.$result.' projects');

    return 0;
  }
}
