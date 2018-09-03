<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Entity\Program;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Catrobat\AppBundle\Commands\Helpers\CommandHelper;

class CleanApkCommand extends ContainerAwareCommand
{
  private $output;

  protected function configure()
  {
    $this->setName('catrobat:clean:apk')
      ->setDescription('Delete the APKs and resets the status to NONE');
  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;

    $this->output->writeln('Deleting APKs');
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.apk.dir'), 'Emptying apk directory', $output);

    /* @var $em \Doctrine\ORM\EntityManager */
    $em = $this->getContainer()->get('doctrine.orm.entity_manager');
    $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p SET p.apk_status = :status WHERE p.apk_status != :status");
    $query->setParameter('status', Program::APK_NONE);
    $result = $query->getSingleScalarResult();
    $this->output->writeln('Reset the apk status of ' . $result . ' projects');
  }
} 