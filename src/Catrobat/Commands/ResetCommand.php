<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use App\Entity\MediaManager;


/**
 * Class ResetCommand
 * @package App\Catrobat\Commands
 */
class ResetCommand extends ContainerAwareCommand
{
  const DOWNLOAD_PROGRAMS_DEFAULT_AMOUNT = 20;

  /**
   *
   */
  protected function configure()
  {
    $this->setName('catrobat:reset')
      ->setDescription('Resets everything to base values')
      ->addOption('hard')
      ->addOption('more', null, InputOption::VALUE_REQUIRED,
        'Downloads the given amount of projets',
        self::DOWNLOAD_PROGRAMS_DEFAULT_AMOUNT)
      ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED,
        'Generates remix graph based on given layout',
        ProgramImportCommand::REMIX_GRAPH_NO_LAYOUT);
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    if (!$input->getOption('hard'))
    {
      $output->writeln("This command will reset everything, use with caution! Use '--hard' option if you are sure.");

      return;
    }

   CommandHelper::executeShellCommand('php bin/console doctrine:schema:drop --force', [],
      'Dropping database', $output);

    CommandHelper::executeShellCommand('php bin/console catrobat:drop:migration', [],
      'Dropping the migration_versions table', $output);
    CommandHelper::executeShellCommand('php bin/console doctrine:migrations:migrate', ['timeout' => 320],
      'Execute the migration to the latest version', $output);
    CommandHelper::executeShellCommand('php bin/console catrobat:create:tags', [],
      'Creating constant tags', $output);

    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.screenshot.dir'),
      'Delete screenshots', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.thumbnail.dir'),
      'Delete thumnails', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.file.storage.dir'),
      'Delete programs', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.file.extract.dir'),
      'Delete extracted programs', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.featuredimage.dir'),
      'Delete featured images', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.mediapackage.dir'),
      'Delete mediapackages', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.template.dir'),
      'Delete templates', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.template.screenshot.dir'),
      'Delete templates-screenshots', $output);

    CommandHelper::executeShellCommand('php bin/console sonata:admin:setup-acl', [],
      'Set up Sonata admin ACL', $output);
    CommandHelper::executeShellCommand('php bin/console sonata:admin:generate-object-acl', [],
      'Generate Sonata object ACL', $output);

    CommandHelper::executeShellCommand('php bin/console catrobat:test:generate --env=test', [],
      'Generating test data', $output);

    CommandHelper::executeShellCommand('php bin/console cache:clear --no-warmup', [],
      'Clearing cache', $output);
    CommandHelper::executeShellCommand('php bin/console cache:clear --env=test', ['timeout' => 120],
      'Resetting Cache', $output);

    CommandHelper::executeShellCommand(
      'php bin/console fos:user:create catroweb catroweb@localhost.at catroweb --super-admin', [],
      'Create default admin user', $output);
    CommandHelper::executeShellCommand('php bin/console fos:user:create user user@localhost.at catroweb', [],
      'Create default user', $output);

    $temp_dir = sys_get_temp_dir() . '/catrobat.program.import/';
    $temp_dir2 = sys_get_temp_dir() . '/catrobat.media.import/';

    $filesystem = new Filesystem();
    $filesystem->remove($temp_dir);
    $filesystem->mkdir($temp_dir);
    $filesystem->remove($temp_dir2);
    $filesystem->mkdir($temp_dir2);

    $this->downloadPrograms($temp_dir, intval($input->getOption('more')), $output);
    $this->downloadMediaLibraries($temp_dir2, intval($input->getOption('more')), $output);

   CommandHelper::executeShellCommand(
      "php bin/console catrobat:mediaimport $temp_dir2",
      ['timeout' => 900], 'Importing Media', $output);
    $filesystem->remove($temp_dir2);

   $remix_layout_option = '--remix-layout=' . intval($input->getOption('remix-layout'));
    CommandHelper::executeShellCommand(
      "php bin/console catrobat:import $temp_dir catroweb $remix_layout_option",
      ['timeout' => 900], 'Importing Projects', $output);
    CommandHelper::executeSymfonyCommand('catrobat:import', $this->getApplication(), [
      'directory'      => $temp_dir,
      'user'           => 'catroweb',
      '--remix-layout' => intval($input->getOption('remix-layout')),
    ], $output);
    $filesystem->remove($temp_dir);

   CommandHelper::executeShellCommand('chmod o+w -R public/resources', [],
      'Setting resources permissions', $output);

    CommandHelper::executeShellCommand('chmod o+w -R public/resources_test', [],
      'Setting test resources permissions', $output);

    CommandHelper::executeShellCommand('chmod o+w+x tests/behat/sqlite/ -R', [],
      'Setting permissions for behat sqlite test database', $output);
  }

  /**
   * @param                 $dir
   * @param                 $amount int The amount of programs to be downloaded
   * @param OutputInterface $output
   */
  private function downloadPrograms($dir, $amount, OutputInterface $output)
  {
    $already_downloaded = 0;

    $output->writeln('Downloading ' . $amount . ' Projects...');

    while ($already_downloaded < $amount)
    {
      $server_json = json_decode(file_get_contents(
        'https://share.catrob.at/app/api/projects/randomProjects.json'), true);
      $base_url = $server_json['CatrobatInformation']['BaseUrl'];
      foreach ($server_json['CatrobatProjects'] as $program)
      {
        if ($already_downloaded === $amount)
        {
          break;
        }

        $url = $base_url . $program['DownloadUrl'];
        $name = $dir . $program['ProjectId'] . '.catrobat';
        $output->writeln('Saving <' . $url . '> to <' . $name . '>');
        try
        {
          file_put_contents($name, file_get_contents($url));
          $already_downloaded++;
        } catch (\Exception $e)
        {
          $output->writeln("File <" . $url . "> returned error 500, continuing...");
          continue;
        }
      }
    }

  }

    /**
   * @param                 $dir
   * @param                 $amount int The amount of media libraries to be downloaded
   * @param OutputInterface $output
   */
  private function downloadMediaLibraries($dir, $amount, OutputInterface $output)
  {
   $output->writeln('Downloading Media Files...');
   
   $server_json = json_decode(file_get_contents('https://share.catrob.at/app/api/media/package/Sounds/json'), true);
   foreach ($server_json as $media)
   {
    $url = 'https://share.catrob.at' . $media['download_url'];
    $name = $dir . $media['name'] . '.media';
    $output->writeln('Downloading ' . $name );
   try
   {
    file_put_contents($name, file_get_contents($url));
   } catch (\Exception $e)
   {
    $output->writeln("File <" . $url . "> returned error 500, continuing...");
    continue;
   }
   }

   $server_json = json_decode(file_get_contents('https://share.catrob.at/app/api/media/package/Looks/json'), true); 
   foreach ($server_json as $media)
   {
    $url = 'https://share.catrob.at' . $media['download_url'];
    $name = $dir . $media['name'] . '.media';
    $output->writeln('Downloading ' . $name );
   try
   {
    file_put_contents($name, file_get_contents($url));
   } catch (\Exception $e)
   {
    $output->writeln("File <" . $url . "> returned error 500, continuing...");
    continue;
   }
   }
  }
}