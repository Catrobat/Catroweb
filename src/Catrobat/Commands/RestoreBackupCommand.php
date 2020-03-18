<?php

namespace App\Catrobat\Commands;

use App\Entity\Program;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Process\Process;

/**
 * Class RestoreBackupCommand.
 */
class RestoreBackupCommand extends Command
{
  /**
   * @var Output
   */
  public $output;

  /**
   * @var EntityManagerInterface
   */
  private $entity_manager;

  /**
   * @var ParameterBagInterface
   */
  private $parameter_bag;

  /**
   * RestoreBackupCommand constructor.
   */
  public function __construct(EntityManagerInterface $entity_manager, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
    $this->entity_manager = $entity_manager;
  }

  protected function configure()
  {
    $this->setName('catrobat:backup:restore')
      ->setDescription('Restores a backup onto a server (ssh). .')
      ->addArgument('file', InputArgument::REQUIRED, 'Backupfile (*.tar.gz)')
      ->addArgument('local', InputArgument::OPTIONAL, 'Add true after the file name restore the backup local. ')
    ;
  }

  /**
   * @throws ExceptionInterface
   * @throws \Doctrine\ORM\NonUniqueResultException | \Exception
   *
   * @return int|void|null
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    $this->output = $output;

    $backup_file = realpath($input->getArgument('file'));
    if (!is_file($backup_file))
    {
      $backup_file = realpath($input->getFirstArgument());

      if (!is_file($backup_file))
      {
        throw new \Exception('File not found');
      }
    }
    $this->output->writeln('Backup File: '.$backup_file);

    if ('pdo_mysql' !== $_ENV['DATABASE_DRIVER'])
    {
      throw new \Exception('This script only supports mysql databases');
    }

    if ($input->hasArgument('local') && 'true' === $input->getArgument('local'))
    {
      $local_resource_directory = $this->parameter_bag->get('catrobat.resources.dir');
      $local_database_name = $_ENV['DATABASE_NAME'];
      $local_database_user = $_ENV['DATABASE_USER'];
      $local_database_password = $_ENV['DATABASE_PASSWORD'];

      $this->output->writeln('Restore backup on localhost');

      $this->executeSymfonyCommand('catrobat:purge', ['--force' => true], $this->output);

      $this->executeShellCommand("time pigz -dc {$backup_file} | tar -xf - -C {$local_resource_directory}",
        'Extracting files to local resources directory');

      $this->executeShellCommand("time mysql -u {$local_database_user} -p{$local_database_password} {$local_database_name} ".
        '< '.$local_resource_directory.'database.sql ',
        'Restore SQL file');

      @unlink($local_resource_directory.'database.sql');

      $query = $this->entity_manager
        ->createQuery('UPDATE App\\Entity\\Program p SET p.apk_status = :status WHERE p.apk_status != :status')
      ;
      $query->setParameter('status', Program::APK_NONE);
      $result = $query->getSingleScalarResult();
      $this->output->writeln('Reset the apk status of '.$result.' projects');

      $query = $this->entity_manager
        ->createQuery('UPDATE App\\Entity\\Program p SET p.directory_hash = :hash WHERE p.directory_hash != :hash')
      ;
      $query->setParameter('hash', 'null');
      $result = $query->getSingleScalarResult();
      $this->output->writeln('Reset the directory hash of '.$result.' projects');
    }
    else
    {
      $backup_host_name = $this->parameter_bag->get('backup_host_name');
      $backup_host_user = $this->parameter_bag->get('backup_host_user');
      $backup_host_password = $this->parameter_bag->get('backup_host_password');
      $backup_host_directory = $this->parameter_bag->get('backup_host_directory');
      $backup_host_resource_directory = $this->parameter_bag->get('backup_host_resource_directory');

      $backup_database_name = $this->parameter_bag->get('backup_database_name');
      $backup_database_user = $this->parameter_bag->get('backup_database_user');
      $backup_database_password = $this->parameter_bag->get('backup_database_password');

      $this->output->writeln('Restore backup on server ['.$backup_host_name.']');

      $this->executeShellCommand("sshpass -p '{$backup_host_password}' ssh {$backup_host_user}@{$backup_host_name} ".
        '"php '.$backup_host_directory.'bin/console catrobat:purge --env=prod --force" ',
        'Remove files from server resources directory');

      $this->executeShellCommand("gzip -dc {$backup_file} | ".
        "sshpass -p '{$backup_host_password}' ssh {$backup_host_user}@{$backup_host_name} ".
        'tar -xf - -C '.$backup_host_directory.$backup_host_resource_directory.' ',
        'Extract files to server resources directory');

      $this->executeShellCommand("sshpass -p '{$backup_host_password}' ssh {$backup_host_user}@{$backup_host_name} ".
        "\"mysql -u {$backup_database_user} -p{$backup_database_password} {$backup_database_name} ".
        '< '.$backup_host_directory.$backup_host_resource_directory.'database.sql" ',
        'Restore SQL file');

      $this->executeShellCommand("sshpass -p '{$backup_host_password}' ssh {$backup_host_user}@{$backup_host_name} ".
        '"unlink '.$backup_host_directory.$backup_host_resource_directory.'database.sql" ',
        'Remove files from server resources directory');

      $this->executeShellCommand("sshpass -p '{$backup_host_password}' ssh {$backup_host_user}@{$backup_host_name} ".
        "\"mysql -u {$backup_database_user} -p{$backup_database_password} {$backup_database_name} ".
        "-e 'UPDATE program p SET p.apk_status = ".Program::APK_NONE.' WHERE p.apk_status != '.Program::APK_NONE."'\" ",
        'Reset the apk status');

      $this->executeShellCommand("sshpass -p '{$backup_host_password}' ssh {$backup_host_user}@{$backup_host_name} ".
        "\"mysql -u {$backup_database_user} -p{$backup_database_password} {$backup_database_name} ".
        "-e 'UPDATE program p SET p.directory_hash = \"null\" WHERE p.directory_hash != \"null\"'\" ",
        'Reset the directory hash');
    }

    $this->output->writeln('Import finished!');
  }

  /**
   * @param $command
   * @param $description
   *
   * @throws \Exception
   *
   * @return bool
   */
  private function executeShellCommand($command, $description)
  {
    $this->output->write($description." ('".$command."') ... ");
    $process = new Process($command);
    $process->setTimeout(86400);
    $process->run();
    if ($process->isSuccessful())
    {
      $this->output->writeln('OK');

      return true;
    }

    throw new \Exception('failed: '.$process->getErrorOutput());
  }

  /**
   * @param $command
   * @param $args
   * @param $output
   *
   * @throws ExceptionInterface
   */
  private function executeSymfonyCommand($command, $args, $output)
  {
    $command = $this->getApplication()->find($command);
    $args['command'] = $command;
    $input = new ArrayInput($args);
    $command->run($input, $output);
  }
}
