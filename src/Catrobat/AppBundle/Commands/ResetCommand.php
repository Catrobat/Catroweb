<?php

namespace Catrobat\AppBundle\Commands;

use Catrobat\AppBundle\Commands\Helpers\CommandHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Finder\Finder;

class ResetCommand extends ContainerAwareCommand
{
  protected function configure()
  {
    $this->setName('catrobat:reset')
      ->setDescription('Resets everything to base values')
      ->addOption('hard')
      ->addOption('more')
      ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED, 'Generates remix graph based on given layout',
        ProgramImportCommand::REMIX_GRAPH_NO_LAYOUT);

  }

  protected function execute(InputInterface $input, OutputInterface $output)
  {
    if (!$input->getOption('hard'))
    {
      $output->writeln("This command will reset everything, use with caution! Use '--hard' option if you are sure.");

      return;
    }

    CommandHelper::executeShellCommand('php app/console doctrine:schema:drop --force', [], 'Dropping database', $output);

    CommandHelper::executeShellCommand('php app/console catrobat:drop:migration', [], 'Dropping the migration_versions table', $output);
    CommandHelper::executeShellCommand('php app/console doctrine:migrations:migrate', [], 'Execute the migration to the latest version', $output);
    CommandHelper::executeShellCommand('php app/console catrobat:create:tags', [], 'Creating constant tags', $output);
    CommandHelper::executeShellCommand('php app/console cache:clear --env=test', ['timeout' => 120], 'Resetting Cache', $output);

    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.screenshot.dir'), 'Delete screenshots', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.thumbnail.dir'), 'Delete thumnails', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.file.storage.dir'), 'Delete programs', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.file.extract.dir'), 'Delete extracted programs', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.featuredimage.dir'), 'Delete featured images', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.mediapackage.dir'), 'Delete mediapackages', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.template.dir'), 'Delete templates', $output);
    CommandHelper::emptyDirectory($this->getContainer()->getParameter('catrobat.template.screenshot.dir'), 'Delete templates-screenshots', $output);

    CommandHelper::executeShellCommand('php app/console sonata:admin:setup-acl', [], 'Init Sonata admin ACL', $output);
    CommandHelper::executeShellCommand('php app/console sonata:admin:generate-object-acl', [], 'Init Sonata object ACL', $output);

    CommandHelper::executeShellCommand('php app/console catrobat:test:generate --env=test', [], 'Generating test data', $output);
    CommandHelper::executeShellCommand('php app/console cache:clear --no-warmup', [], 'Clearing cache', $output);

    CommandHelper::executeShellCommand('php app/console fos:user:create catroweb catroweb@localhost catroweb --super-admin', [], 'Create default admin user', $output);

    $temp_dir = sys_get_temp_dir() . '/catrobat.program.import/';

    $filesystem = new Filesystem();
    $filesystem->remove($temp_dir);
    $filesystem->mkdir($temp_dir);
    if ($input->getOption('more'))
    {
      $this->downloadMorePrograms($temp_dir, $output);
    }
    else
    {
      $this->downloadPrograms($temp_dir, $output);
    }
    $remix_layout_option = '--remix-layout=' . intval($input->getOption('remix-layout'));
    CommandHelper::executeShellCommand("php app/console catrobat:import $temp_dir catroweb $remix_layout_option",
      [], 'Importing Projects', $output);
    CommandHelper::executeSymfonyCommand('catrobat:import', $this->getApplication(), [
      'directory'      => $temp_dir,
      'user'           => 'catroweb',
      '--remix-layout' => intval($input->getOption('remix-layout')),
    ], $output);
    $filesystem->remove($temp_dir);

    CommandHelper::executeShellCommand('chmod o+w -R web/resources', [], 'Setting permissions', $output);
  }

  private function downloadPrograms($dir, OutputInterface $output)
  {
    $server_json = json_decode(file_get_contents('https://share.catrob.at/pocketcode/api/projects/recent.json'), true);
    $base_url = $server_json['CatrobatInformation']['BaseUrl'];
    foreach ($server_json['CatrobatProjects'] as $program)
    {
      $url = $base_url . $program['DownloadUrl'];
      $name = $dir . intval($program['ProjectId']) . '.catrobat';
      $output->writeln('Saving <' . $url . '> to <' . $name . '>');
      try
      {
        file_put_contents($name, file_get_contents($url));
      } catch (\ErrorException $e)
      {
        $output->writeln("File <" . $url . "> returned error 500, continuing...");
        continue;
      }
    }
  }

  private function downloadMorePrograms($dir, OutputInterface $output)
  {
    for ($i = 0; $i < 10; $i++)
    {
      $server_json = json_decode(file_get_contents('https://share.catrob.at/pocketcode/api/projects/randomPrograms.json'), true);
      $base_url = $server_json['CatrobatInformation']['BaseUrl'];
      foreach ($server_json['CatrobatProjects'] as $program)
      {
        $url = $base_url . $program['DownloadUrl'];
        $name = $dir . intval($program['ProjectId']) . '.catrobat';
        $output->writeln('Saving <' . $url . '> to <' . $name . '>');
        try
        {
          file_put_contents($name, file_get_contents($url));
        } catch (\ErrorException $e)
        {
          $output->writeln("File <" . $url . "> returned error 500, continuing...");
          continue;
        }
      }
    }

  }
}
