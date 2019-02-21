<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Entity\Program;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Finder\Finder;


define("HOURS", 24);
define("MINUTES", 60);
define("SECONDS", 60);


/**
 * Class CleanOldApkCommand
 * @package Catrobat\AppBundle\Commands
 */
class CleanOldApkCommand extends ContainerAwareCommand
{
  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:clean:old-apk')
      ->setDescription('Delete all APKs older than X days and resets the status to NONE')
      ->addArgument('days');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|null
   * @throws \Doctrine\ORM\NonUniqueResultException
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $days = $input->getArgument('days');

    if (!is_numeric($days))
    {
      $output->writeln('You have to enter a numeric value as parameter!');

      return -1;
    }

    $output->writeln('Deleting all APKs older than ' . $days . ' days.');
    $last_point_of_time_to_save = time() - ((int)$days * HOURS * MINUTES * SECONDS);

    $directory = $this->getContainer()->getParameter('catrobat.apk.dir');
    $filesystem = new Filesystem();
    $finder = new Finder();
    $finder->in($directory)->depth(0);
    $removed_apk_ids = new \ArrayObject();
    $amount_of_files = sizeOf($finder);

    foreach ($finder as $file)
    {
      $access_time = $file->getATime();
      if ($access_time < $last_point_of_time_to_save)
      {
        $filesystem->remove($file);
        $removed_apk_ids->append(explode('.', $file->getFilename())[0]);
      }
    }

    $output->writeln('Files removed (' . sizeOf($removed_apk_ids) . '/' . $amount_of_files . ')');

    if (!sizeOf($removed_apk_ids))
    {
      $output->writeln('No projects have been reset.');

      return 0;
    }
    $query = $this->createQueryToUpdateTheStatusOfRemovedApks($removed_apk_ids);
    $result = $query->getSingleScalarResult();
    $output->writeln('Reset the apk status of ' . $result . ' projects');

    return 0;
  }


  /**
   * @param $removed_apk_ids
   *
   * @return \Doctrine\ORM\Query
   */
  private function createQueryToUpdateTheStatusOfRemovedApks($removed_apk_ids)
  {
    /**
     * @var $em \Doctrine\ORM\EntityManager
     */
    $id_query_part = '';
    $i = 0;
    foreach ($removed_apk_ids as $apk_id)
    {
      if ($i != 0)
      {
        $id_query_part .= 'OR ';
      }
      $id_query_part .= 'p.id = ' . $apk_id . ' ';
      $i++;
    }

    if ($id_query_part != '')
    {
      $id_query_part = ' AND (' . $id_query_part . ')';
    }

    $em = $this->getContainer()->get('doctrine.orm.entity_manager');
    $query = $em->createQuery("UPDATE Catrobat\AppBundle\Entity\Program p 
                      SET p.apk_status = :status WHERE p.apk_status != :status" . $id_query_part);
    $query->setParameter('status', Program::APK_NONE);

    return $query;
  }
}