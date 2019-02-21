<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Catrobat\AppBundle\Commands\Helpers\CommandHelper;


/**
 * Class CleanExtractedFileCommand
 * @package Catrobat\AppBundle\Commands
 */
class CleanExtractedFileCommand extends ContainerAwareCommand
{
  /**
   * @var
   */
  private $output;

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
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.file.extract.dir'), 'Emptying extracted directory', $output);

    /* @var $em \Doctrine\ORM\EntityManager */
    $em = $this->getContainer()->get('doctrine.orm.entity_manager');
    $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p SET p.directory_hash = :hash WHERE p.directory_hash != :hash");
    $query->setParameter('hash', "null");
    $result = $query->getSingleScalarResult();
    $this->output->writeln('Reset the directory hash of ' . $result . ' projects');
  }
} 