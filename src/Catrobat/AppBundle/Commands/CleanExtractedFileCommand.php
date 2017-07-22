<?php

namespace Catrobat\AppBundle\Commands;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;
use Catrobat\AppBundle\Commands\Helpers\CommandHelper;

class CleanExtractedFileCommand extends ContainerAwareCommand
{
  private $output;

  protected function configure()
  {
    $this->setName('catrobat:clean:extracted')
         ->setDescription('Delete the extracted programs and sets the directory hash to NULL');
  }

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
    $this->output->writeln('Reset the directory hash of '.$result.' projects');
  }
} 