<?php

declare(strict_types=1);

namespace App\System\Commands\Maintenance;

use App\DB\Entity\Project\Program;
use App\Utils\TimeUtils;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

#[AsCommand(name: 'catrobat:clean:old-apk', description: 'Delete all APKs older than X days and resets the status to NONE')]
class CleanOldApkCommand extends Command
{
  private const int HOURS = 24;

  private const int MINUTES = 60;

  private const int SECONDS = 60;

  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addArgument('days')
    ;
  }

  /**
   * @throws \Exception
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    $days = $input->getArgument('days');

    if (!is_numeric($days)) {
      $output->writeln('To delete all APKs older than X days you have to enter a numeric value as parameter!');

      return 1;
    }

    $output->writeln('Deleting all APKs older than '.$days.' days.');
    $last_point_of_time_to_save = TimeUtils::getTimestamp() - ((int) $days * self::HOURS * self::MINUTES * self::SECONDS);

    $directory = $this->parameter_bag->get('catrobat.apk.dir');
    $finder = new Finder();
    $finder->in($directory)->depth(0);
    $removed_apk_ids = new \ArrayObject();
    $amount_of_files = count($finder);

    /** @var SplFileInfo $file */
    foreach ($finder as $file) {
      $access_time = $file->getATime();
      if ($access_time < $last_point_of_time_to_save) {
        unlink($file->__toString());
        $removed_apk_ids->append(explode('.', $file->getFilename())[0]);
      }
    }

    $output->writeln('Files removed ('.count($removed_apk_ids).'/'.$amount_of_files.')');

    if (0 === count($removed_apk_ids)) {
      $output->writeln('No projects have been reset.');

      return 0;
    }

    $query = $this->createQueryToUpdateTheStatusOfRemovedApks($removed_apk_ids);
    $result = $query->getSingleScalarResult();
    $output->writeln('Reset the apk status of '.$result.' projects');

    return 0;
  }

  private function createQueryToUpdateTheStatusOfRemovedApks(mixed $removed_apk_ids): Query
  {
    $id_query_part = '';
    $i = 0;
    foreach ($removed_apk_ids as $apk_id) {
      if (0 !== $i) {
        $id_query_part .= 'OR ';
      }

      $id_query_part .= "p.id = '".$apk_id."' ";
      ++$i;
    }

    if ('' !== $id_query_part) {
      $id_query_part = ' AND ('.$id_query_part.')';
    }

    $query = $this->entity_manager->createQuery(
      'UPDATE App\DB\Entity\Project\Program p SET p.apk_status = :status WHERE p.apk_status != :status'.$id_query_part
    );
    $query->setParameter('status', Program::APK_NONE);

    return $query;
  }
}
