<?php

namespace App\Catrobat\Commands;

use App\Entity\Program;
use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Catrobat\Commands\Helpers\CommandHelper;


/**
 * Class CleanApkCommand
 * @package App\Catrobat\Commands
 */
class CleanApkCommand extends ContainerAwareCommand
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
    $this->setName('catrobat:clean:apk')
      ->setDescription('Delete the APKs and resets the status to NONE');
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

    $this->output->writeln('Deleting APKs');
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.apk.dir'), 'Emptying apk directory', $output);

    /* @var $em EntityManager */
    $em = $this->getContainer()->get('doctrine')->getManager();
    $query = $em->createQuery("UPDATE App\Entity\Program p SET p.apk_status = :status WHERE p.apk_status != :status");
    $query->setParameter('status', Program::APK_NONE);
    $result = $query->getSingleScalarResult();
    $this->output->writeln('Reset the apk status of ' . $result . ' projects');
  }
} 