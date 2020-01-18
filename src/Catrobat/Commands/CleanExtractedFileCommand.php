<?php

namespace App\Catrobat\Commands;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Catrobat\Commands\Helpers\CommandHelper;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;


/**
 * Class CleanExtractedFileCommand
 * @package App\Catrobat\Commands
 */
class CleanExtractedFileCommand extends Command
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
   * CleanExtractedFileCommand constructor.
   *
   * @param EntityManagerInterface $entity_manager
   * @param ParameterBagInterface $parameter_bag
   */
  public function __construct(EntityManagerInterface $entity_manager, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
    $this->entity_manager = $entity_manager;
  }

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:clean:extracted')
      ->setDescription('Delete the extracted programs and sets the directory hash to NULL');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;

    $this->output->writeln('Deleting Extracted Catrobat Files');
    CommandHelper::emptyDirectory($this->parameter_bag->get('catrobat.file.extract.dir'), 'Emptying extracted directory', $output);

    $query = $this->entity_manager
      ->createQuery("UPDATE App\Entity\Program p SET p.directory_hash = :hash WHERE p.directory_hash != :hash");
    $query->setParameter('hash', "null");
    $result = $query->getSingleScalarResult();
    $this->output->writeln('Reset the directory hash of ' . $result . ' projects');
  }
} 