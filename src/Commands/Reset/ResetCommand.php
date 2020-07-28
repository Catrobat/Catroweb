<?php

namespace App\Commands\Reset;

use App\Commands\Helpers\CommandHelper;
use App\Commands\ImportProjects\ProgramImportCommand;
use App\Entity\Program;
use App\Repository\ProgramRepository;
use Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class ResetCommand extends Command
{
  const DOWNLOAD_PROGRAMS_DEFAULT_AMOUNT = 30;

  protected static $defaultName = 'catrobat:reset';

  private array $reported = [];

  private ParameterBagInterface $parameter_bag;

  private ProgramRepository $program_manager;

  public function __construct(ProgramRepository $program_manager, ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->parameter_bag = $parameter_bag;
    $this->program_manager = $program_manager;
  }

  protected function configure(): void
  {
    $this->setName('catrobat:reset')
      ->setDescription('Resets everything to base values')
      ->addOption('hard')
      ->addOption('limit', null, InputOption::VALUE_REQUIRED,
        'Downloads the given amount of projects',
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

      return 1;
    }

    // Setting up the project permissions
    CommandHelper::executeShellCommand(
        ['sh', 'docker/app/set-permissions.sh'], ['timeout' => 320], 'Setting up permissions', $output
    );

    // Delete data and recreate clean DB
    CommandHelper::executeSymfonyCommand(
      'catrobat:purge', $this->getApplication(), ['--force' => true], $output
    );

    // Tags implementation is very broken -> this command is needed to create them
    CommandHelper::executeShellCommand(
        ['bin/console', 'catrobat:create:tags'], [], 'Creating constant tags', $output
    );

    // SetUp Acl
    CommandHelper::executeShellCommand(
        ['bin/console', 'sonata:admin:setup-acl'], [], 'Set up Sonata admin ACL', $output
    );

    $this->clearCache($output);

    $user_array = [
      'catroweb', 'user', 'Elliot', 'Darlene', 'Angela', 'Tyrell', 'Edward', 'Price', 'Dom', 'ZhiZhang', 'Irving',
      'Janice', 'Vera', 'Sam', 'TheHero', 'Esmail',
    ];

    $this->createUsers($user_array, $output);

    $this->importProjectsFromShare(
      intval($input->getOption('limit')),
      $user_array,
      intval($input->getOption('remix-layout')),
      $output
    );

    $programs = $this->program_manager->findAll();
    $program_names = [];
    /** @var Program $program */
    foreach ($programs as $program)
    {
      array_push($program_names, $program->getName());
    }

    $this->reportProjects($program_names, $user_array, $output);
    $this->remixGen($program_names, $output);
    $this->commentOnProjects($program_names, $user_array, $output);
    $this->likeProjects($program_names, $user_array, $output);
    $this->featureProjects($program_names, $output);
    $this->followUsers($user_array, $output);
    $this->downloadProjects($program_names, $user_array, $output);

    //https://share.catrob.at/app/project/{id_of_project}/remix_graph_data to get remixes

    // Creating sample MediaPackages
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:create:media-packages-samples'], [], 'Creating sample Media Packages', $output
    );

    // Resetting Elastic
    CommandHelper::executeShellCommand(
      ['bin/console', 'fos:elastica:reset', '-q'], [], 'Resetting', $output
    );
    CommandHelper::executeShellCommand(
      ['bin/console', 'fos:elastica:populate', '-q'], [], 'Populating data', $output
    );

    $output->writeln('Reset Done');

    return 0;
  }

  private function clearCache(OutputInterface $output): void
  {
    CommandHelper::executeShellCommand(
      ['bin/console', 'cache:clear', '--env=dev'], [], 'Clearing dev cache', $output
    );
    CommandHelper::executeShellCommand(
      ['bin/console', 'cache:clear', '--env=test'], ['timeout' => 120], 'Clearing test cache', $output
    );
  }

  private function createUsers(array $user_array, OutputInterface $output): void
  {
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
  }

  /**
   * @throws Exception
   */
  private function importProjectsFromShare(int $limit, array $user_array, int $remix_layout, OutputInterface $output): void
  {
    if ($limit < 0)
    {
      $limit = 0;
    }

    $projects_to_download = $limit;
    while ($projects_to_download > 0)
    {
      $amount = random_int(1, intval(floor($projects_to_download / 5)) + 1);
      $this->userUploadProjects($amount, $user_array[random_int(0, sizeof($user_array) - 1)], $remix_layout, $output);
      $projects_to_download -= $amount;
    }
  }

  /**
   * @throws Exception
   */
  private function userUploadProjects(int $limit, string $username, int $remix_layout, OutputInterface $output): int
  {
    return CommandHelper::executeSymfonyCommand(
      'catrobat:import:share',
      $this->getApplication(),
      [
        '--limit' => $limit,
        '--user' => $username,
        '--remix-layout' => $remix_layout,
      ],
      $output
    );
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
          $output->writeln('Comment creation failed for '.json_encode($parameters).' error code: '.$ret);
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
        $output->writeln('Report project creation failed for '.json_encode($parameters).' error code: '.$ret);
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
          $output->writeln('Project like creation failed for '.json_encode($parameters).' error code: '.$ret);
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
          $output->writeln('Project Download creation failed for '.json_encode($parameters).' error code: '.$ret);
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
        $output->writeln('Setting project to featured failed for '.json_encode($parameters).' error code: '.$ret);
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
        $output->writeln('Follow Action failed for '.json_encode($parameters).' error code: '.$ret);
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
        $output->writeln('Remix Action failed for '.json_encode($parameters).' error code: '.$ret);
      }
    }
  }
}
