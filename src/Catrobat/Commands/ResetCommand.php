<?php

namespace App\Catrobat\Commands;

use App\Catrobat\Commands\Helpers\CommandHelper;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Filesystem\Filesystem;

class ResetCommand extends Command
{
  const DOWNLOAD_PROGRAMS_DEFAULT_AMOUNT = 30;

  protected static $defaultName = 'catrobat:reset';

  private array $reported = [];

  private ParameterBagInterface $parameter_bag;

  public function __construct(ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:reset')
      ->setDescription('Resets everything to base values')
      ->addOption('hard')
      ->addOption('more', null, InputOption::VALUE_REQUIRED,
        'Downloads the given amount of projets',
        self::DOWNLOAD_PROGRAMS_DEFAULT_AMOUNT)
      ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED,
        'Generates remix graph based on given layout',
        ProgramImportCommand::REMIX_GRAPH_NO_LAYOUT)
    ;
  }

  /**
   * @throws Exception
   */
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    if (!$input->getOption('hard'))
    {
      $output->writeln("This command will reset everything, use with caution! Use '--hard' option if you are sure.");

      return -1;
    }

    // Setting up the project permissions
    CommandHelper::executeShellCommand(
        ['sh', 'docker/app/set-permissions.sh'], [], 'Setting up permissions', $output
    );

    // Rebuild the database
    CommandHelper::executeShellCommand(
        ['bin/console', 'doctrine:schema:drop', '--force'], [], 'Dropping database', $output
    );
    CommandHelper::executeShellCommand(
        ['bin/console', 'catrobat:drop:migration'], [], 'Dropping the migration_versions table', $output
    );
    CommandHelper::executeShellCommand(
        ['bin/console', 'doctrine:migrations:migrate', '--no-interaction'], ['timeout' => 320],
      'Execute the migration to the latest version', $output
    );
    CommandHelper::executeShellCommand(
        ['bin/console', 'catrobat:create:tags'], [], 'Creating constant tags', $output
    );

    // Clear old files
    CommandHelper::emptyDirectory(
      $this->parameter_bag->get('catrobat.apk.dir'), 'Deleting APKs', $output
    );
    CommandHelper::emptyDirectory(
      $this->parameter_bag->get('catrobat.file.extract.dir'), 'Delete extracted programs', $output
    );
    CommandHelper::emptyDirectory(
      $this->parameter_bag->get('catrobat.featuredimage.dir'), 'Delete featured images', $output
    );
    CommandHelper::emptyDirectory(
      $this->parameter_bag->get('catrobat.mediapackage.dir'), 'Delete media packages', $output
    );
    CommandHelper::emptyDirectory(
      $this->parameter_bag->get('catrobat.file.storage.dir'), 'Delete programs', $output
    );
    CommandHelper::emptyDirectory(
        $this->parameter_bag->get('catrobat.screenshot.dir'), 'Delete screenshots', $output
    );
    CommandHelper::emptyDirectory(
      $this->parameter_bag->get('catrobat.template.dir'), 'Delete templates', $output
    );
    CommandHelper::emptyDirectory(
        $this->parameter_bag->get('catrobat.thumbnail.dir'), 'Delete thumbnails', $output
    );

    // SetUp Acl
    CommandHelper::executeShellCommand(
        ['bin/console', 'sonata:admin:setup-acl'], [], 'Set up Sonata admin ACL', $output
    );

    // Generate test data
    CommandHelper::executeShellCommand(
        ['bin/console', 'catrobat:test:generate', '--env=test', '--no-interaction'], [],
      'Generating test data', $output
    );

    // Clear caches
    CommandHelper::executeShellCommand(
        ['bin/console', 'cache:clear', '--env=dev'], [], 'Clearing dev cache', $output
    );
    CommandHelper::executeShellCommand(
        ['bin/console', 'cache:clear', '--env=test'], ['timeout' => 120], 'Clearing test cache', $output
    );

    // Create Users  with their projects and add interactions.

    $user_array = ['catroweb', 'user', 'Elliot', 'Darlene', 'Angela', 'Tyrell', 'Edward', 'Price', 'Dom',
      'ZhiZhang', 'Irving', 'Janice', 'Vera', 'Sam', 'TheHero', 'Esmail', ];

    $password = 'catroweb';

    CommandHelper::executeShellCommand(
      ['bin/console', 'fos:user:create', 'catroweb', 'catroweb@localhost.at', $password, '--super-admin'],
      ['timeout' => 300], 'Create default admin user named catroweb with password catroweb', $output
    );

    for ($i = 1; $i < sizeof($user_array); ++$i)
    { //starting at one because of admin user
      CommandHelper::executeShellCommand(
          ['bin/console', 'fos:user:create', $user_array[$i], $user_array[$i].'@localhost.at', $password],
          ['timeout' => 300], 'Create default user named '.$user_array[$i].' with password catroweb', $output
      );
    }

    $temp_dir = sys_get_temp_dir().'/catrobat.program.import/';

    $filesystem = new Filesystem();
    $filesystem->remove($temp_dir);

    foreach ($user_array as $user)
    {
      $filesystem->mkdir($temp_dir.$user);
    }

    $program_names = $this->downloadPrograms($temp_dir, intval($input->getOption('more')), $output, $user_array);

    $remix_layout_option = '--remix-layout='.intval($input->getOption('remix-layout'));
    foreach ($user_array as $user)
    {
      $temp_dir_user = $temp_dir.$user;
      CommandHelper::executeShellCommand(
        ['bin/console', 'catrobat:import', $temp_dir_user, $user, $remix_layout_option], ['timeout' => 900],
        'Importing Projects', $output
      );
      CommandHelper::executeSymfonyCommand('catrobat:import', $this->getApplication(), [
        'directory' => $temp_dir_user,
        'user' => $user,
        '--remix-layout' => intval($input->getOption('remix-layout')),
      ], $output);

      $filesystem->remove($temp_dir_user);
      $output->writeln("Removing directory {$temp_dir_user}");
    }

    $this->reportProjects($program_names, $user_array, $output);
    $this->remixGen($program_names, $output);
    $this->commentOnProjects($program_names, $user_array, $output);
    $this->likeProjects($program_names, $user_array, $output);
    $this->featureProjects($program_names, $output);
    $this->followUsers($user_array, $output);
    $this->downloadProjects($program_names, $user_array, $output);

    //https://share.catrob.at/app/project/remixgraph/{id_of_project} to get remixes

    // Creating sample MediaPackages
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:create:media-packages-samples'], [], 'Creating sample Media Packages', $output
    );

    echo "Reset Done\n";

    return 0;
  }

  private function downloadPrograms(string $dir, int $amount, OutputInterface $output, array $user_array = null): array
  {
    $already_downloaded = 0;
    $user = 0;
    $program_names = [];

    $output->writeln('Downloading '.$amount.' Projects...');

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

        $url = $base_url.$program['DownloadUrl'];
        if (null !== $user_array)
        {
          $name = $dir.$user_array[$user % sizeof($user_array)].'/'.$program['ProjectId'].'.catrobat';
          $program_names[$already_downloaded] = $program['ProjectName'];
          ++$user;
        }
        else
        {
          $name = $dir.$program['ProjectId'].'.catrobat';
        }
        $output->writeln('Saving <'.$url.'> to <'.$name.'>');
        try
        {
          file_put_contents($name, file_get_contents($url));
          ++$already_downloaded;
        }
        catch (Exception $e)
        {
          $output->writeln('File <'.$url.'> returned error 500, continuing...');
          continue;
        }
      }
    }

    return $program_names;
  }

  private function randomCommentGenerator(): string
  {
    $first = ['I am', 'You are', 'He is', 'They are', 'She is', 'It is', 'We are', 'This is'];
    $second = [' ', ' not ', ' extremly '];
    $third = ['good', 'hideous', 'fabulous', 'amazing', 'cute', 'lovely'];
    $fourth = [' ;)', ' :D', ' :(', ' xD', ' :O'];

    return $first[array_rand($first)].$second[array_rand($second)].$third[array_rand($third)].$fourth[array_rand($fourth)];
  }

  /**
   * @throws Exception
   */
  private function commentOnProjects(array $program_names, array $user_array, OutputInterface $output): void
  {
    $i = 0;
    foreach ($program_names as $program_name)
    {
      $random_reported = random_int(-10, 1);
      if ($random_reported <= 0)
      {
        $random_reported = 0;
      }
      $random_comment_amount = random_int(0, 3);
      for ($j = 0; $j <= $random_comment_amount; ++$j)
      {
        $user_id = array_rand($user_array);
        $parameters = [
          'user' => $user_array[$user_id],
          'program_name' => $program_name,
          'message' => $this->randomCommentGenerator(),
          'reported' => $random_reported,
        ];

        $ret = CommandHelper::executeSymfonyCommand('catrobat:comment', $this->getApplication(), $parameters, $output);
        if (0 !== $ret)
        {
          echo 'Comment creation failed for '.json_encode($parameters);
        }
      }
      ++$i;
    }
  }

  /**
   * @throws Exception
   */
  private function reportProjects(array $program_names, array $user_array, OutputInterface $output): void
  {
    $rand_start = random_int(0, 2);
    $rand_interval = random_int(4, 6);

    for ($i = $rand_start; $i < sizeof($program_names); $i += $rand_interval)
    {
      $this->reported[sizeof($this->reported)] = $i;
      $parameters = [
        'user' => $user_array[array_rand($user_array)],
        'program_name' => $program_names[$i],
        'note' => 'bad',
      ];
      $ret = CommandHelper::executeSymfonyCommand('catrobat:report', $this->getApplication(), $parameters, $output);

      if (0 !== $ret)
      {
        echo 'Report project creation failed for '.json_encode($parameters);
      }
    }
  }

  /**
   * @throws Exception
   */
  private function likeProjects(array $program_names, array $user_array, OutputInterface $output): void
  {
    foreach ($program_names as $program_name)
    {
      $like_amount = random_int(1, 5);
      for ($i = 0; $i < $like_amount; ++$i)
      {
        $parameters = [
          'program_name' => $program_name,
          'user_name' => $user_array[($i + $like_amount) % sizeof($user_array)],
        ];
        $ret = CommandHelper::executeSymfonyCommand('catrobat:like', $this->getApplication(), $parameters, $output);

        if (0 !== $ret)
        {
          echo 'Project like creation failed for '.json_encode($parameters);
        }
      }
    }
  }

  /**
   * @throws Exception
   */
  private function downloadProjects(array $program_names, array $user_array, OutputInterface $output): void
  {
    foreach ($program_names as $program_name)
    {
      $download_amount = random_int(1, 5);
      for ($i = 0; $i < $download_amount; ++$i)
      {
        $parameters = [
          'program_name' => $program_name,
          'user_name' => $user_array[array_rand($user_array)],
        ];
        $ret = CommandHelper::executeSymfonyCommand('catrobat:download', $this->getApplication(), $parameters, $output);

        if (0 !== $ret)
        {
          echo 'Project Download creation failed for '.json_encode($parameters);
        }
      }
    }
  }

  /**
   * @throws Exception
   */
  private function featureProjects(array $program_names, OutputInterface $output): void
  {
    $rand_start = random_int(1, 2);
    $rand_interval = random_int(4, 6);

    for ($i = $rand_start; $i < sizeof($program_names); $i += $rand_interval)
    {
      $parameters = [
        'program_name' => $program_names[$i % sizeof($program_names)],
      ];
      $ret = CommandHelper::executeSymfonyCommand('catrobat:feature', $this->getApplication(), $parameters, $output);

      if (0 !== $ret)
      {
        // Might fail because of missing screenshots!
        echo 'Setting project to featured failed for '.json_encode($parameters);
      }
    }
  }

  /**
   * @throws Exception
   */
  private function followUsers(array $user_array, OutputInterface $output): void
  {
    for ($i = 0; $i < sizeof($user_array); ++$i)
    {
      $user_id = $i;
      $follower_id = random_int(0, sizeof($user_array) - 1);

      $parameters = [
        'user_name' => $user_array[$user_id],
        'follower' => $user_array[$follower_id],
      ];
      $ret = CommandHelper::executeSymfonyCommand('catrobat:follow', $this->getApplication(), $parameters, $output);

      if (0 !== $ret)
      {
        echo 'Follow Action failed for '.json_encode($parameters);
      }
    }
  }

  /**
   * @throws Exception
   */
  private function remixGen(array $program_array, OutputInterface $output): void
  {
    $rand_start = random_int(2, 3);
    $rand_intervall = random_int(3, 6);

    for ($i = $rand_start; $i < sizeof($program_array); $i += $rand_intervall)
    {
      $report_index = ($i + random_int(1, sizeof($program_array))) % sizeof($program_array);
      if (in_array($i, $this->reported, true) || in_array($report_index, $this->reported, true))
      {
        $i = $i - $rand_intervall + 1;
        continue;
      }

      $parameters = [
        'program_original' => $program_array[$i],
        'program_remix' => $program_array[$report_index],
      ];

      $ret = CommandHelper::executeSymfonyCommand('catrobat:remix', $this->getApplication(), $parameters, $output);

      if (0 !== $ret)
      {
        echo 'Follow Action failed for '.json_encode($parameters);
      }
    }
  }
}
