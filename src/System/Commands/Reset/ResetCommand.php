<?php

declare(strict_types=1);

namespace App\System\Commands\Reset;

use App\DB\Entity\FeaturedBanner;
use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\Project\Program;
use App\DB\Entity\System\Statistic;
use App\DB\Entity\User\Comment\UserComment;
use App\DB\EntityRepository\Project\ProgramRepository;
use App\DB\EntityRepository\System\StatisticRepository;
use App\DB\Enum\ContentType;
use App\DB\Enum\ReportCategory;
use App\System\Commands\Helpers\CommandHelper;
use App\System\Commands\ImportProjects\ProgramImportCommand;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Random\RandomException;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\ExceptionInterface;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

#[AsCommand(name: 'catrobat:reset', description: 'Resets everything to base values')]
class ResetCommand extends Command
{
  final public const string DOWNLOAD_PROGRAMS_DEFAULT_AMOUNT = '30';

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly ProgramRepository $program_manager,
    private readonly StatisticRepository $statistic_repository,
    private readonly ParameterBagInterface $parameter_bag,
    private readonly UserManager $user_manager)
  {
    parent::__construct();
  }

  #[\Override]
  protected function configure(): void
  {
    $this
      ->addOption('hard')
      ->addOption('limit', null, InputOption::VALUE_REQUIRED,
        'Downloads the given amount of projects',
        self::DOWNLOAD_PROGRAMS_DEFAULT_AMOUNT)
      ->addOption('remix-layout', null, InputOption::VALUE_REQUIRED,
        'Generates remix graph based on given layout',
        ProgramImportCommand::REMIX_GRAPH_NO_LAYOUT)
      ->addOption('with-remixes', null, InputOption::VALUE_NONE,
        'Should projects have remixes?')
    ;
  }

  /**
   * @throws ExceptionInterface
   * @throws RandomException
   * @throws \JsonException
   */
  #[\Override]
  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    if (!$input->getOption('hard')) {
      $output->writeln("This command will reset everything, use with caution! Use '--hard' option if you are sure.");

      return 1;
    }

    // Setting up the project permissions
    CommandHelper::executeShellCommand(
      ['sh', 'docker/app/set-permissions.sh'], ['timeout' => 320], 'Setting up permissions', $output
    );

    // Delete data and recreate clean DB
    CommandHelper::executeSymfonyCommand(
      'catrobat:purge', $this->getApplicationOrFail(), ['--force' => true], $output
    );

    // Create static flavors
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:flavors'], [], 'Creating constant flavors', $output
    );

    // Create static tags
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:tags'], [], 'Creating constant tags', $output
    );

    // Create static extensions
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:extensions'], [], 'Creating constant tags', $output
    );

    $this->clearCache($output);

    $user_array = [
      'catroweb', 'user', 'Elliot', 'Darlene', 'Angela', 'Tyrell', 'Edward', 'Price', 'Dom', 'ZhiZhang', 'Irving',
      'Janice', 'Vera', 'Sam', 'TheHero', 'Esmail',
    ];

    $this->createUsers($user_array, $output);
    $share_projects_import = $this->importProjectsFromShare(
      intval($input->getOption('limit')),
      $user_array,
      intval($input->getOption('remix-layout')),
      $output
    );

    if (!$share_projects_import) {
      /** @var string $resources_dir */
      $resources_dir = $this->parameter_bag->get('catrobat.resources.dir');
      $local_projects_dir = $resources_dir.'projects';
      $local_projects_import = $this->importLocalProjects(
        $local_projects_dir,
        20,
        $user_array,
        intval($input->getOption('remix-layout')),
        $output
      );

      if (!$local_projects_import) {
        throw new \Exception('Projects could neither be loaded from the share nor from '.$local_projects_dir);
      }
    }

    $programs = $this->program_manager->findAll();
    $program_names = [];
    /** @var Program $program */
    foreach ($programs as $program) {
      $program_names[] = $program->getName();
    }

    if ([] !== $programs) {
      $this->createStudios($user_array, $programs, $output);
    }

    $this->createModerationData($programs, $user_array, $output);
    // if ($input->hasOption('with-remixes')) {
    // $this->remixGen($program_names, $output);  // Currently not working
    // }

    $this->commentOnProjects($program_names, $user_array, $output);
    $this->likeProjects($program_names, $user_array, $output);
    $this->featureProjects($program_names, $output);
    $this->createFeaturedBanners($programs, $output);
    $this->followUsers($user_array, $output);
    $this->downloadProjects($program_names, $user_array, $output);
    $this->exampleProject($program_names, $output);
    $this->markNotForKids($program_names, $output);
    $this->addStatistics();

    // https://share.catrob.at/app/api/project/{id_of_project}/remix-graph to inspect remixes

    // Creating sample Media Samples
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:create:media-assets-samples'], [], 'Creating media asset samples', $output
    );

    // Insert static achievements
    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:achievements'], [], 'Creating Achievements', $output
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
      ['bin/console', 'cache:clear', '--env=dev'], ['timeout' => 240], 'Clearing dev cache', $output
    );
    CommandHelper::executeShellCommand(
      ['bin/console', 'cache:clear', '--env=test'], ['timeout' => 240], 'Clearing test cache', $output
    );
  }

  /**
   * @param non-empty-array<string> $user_array
   */
  private function createUsers(array $user_array, OutputInterface $output): void
  {
    $password = 'catroweb';

    CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:user:create', 'catroweb', 'catroweb@localhost.at', $password, '--super-admin'],
      ['timeout' => 300], 'Create default admin user named catroweb with password catroweb', $output
    );
    $counter = count($user_array);

    for ($i = 1; $i < $counter; ++$i) { // starting at one because of admin user
      CommandHelper::executeShellCommand(
        ['bin/console', 'catrobat:user:create', $user_array[$i], $user_array[$i].'@localhost.at', $password],
        ['timeout' => 300], 'Create default user named '.$user_array[$i].' with password catroweb', $output
      );
    }
  }

  /**
   * @param non-empty-array<string> $user_array
   *
   * @throws ExceptionInterface
   * @throws RandomException
   */
  private function importLocalProjects(string $local_projects_dir, int $limit, array $user_array, int $remix_layout, OutputInterface $output): bool
  {
    if ($limit < 0) {
      $limit = 0;
    }

    $projects_remaining = $limit;
    while ($projects_remaining > 0) {
      $amount = random_int(1, max(1, intval(floor($projects_remaining / 5)) + 1));
      $username = $user_array[random_int(0, count($user_array) - 1)];

      CommandHelper::executeSymfonyCommand('catrobat:import', $this->getApplicationOrFail(),
        [
          'directory' => $local_projects_dir,
          'user' => $username,
          '--remix-layout' => $remix_layout,
          '--limit' => $amount,
        ],
        $output
      );
      $projects_remaining -= $amount;
    }

    $projects_count = count($this->program_manager->findAll());

    return $projects_count > 0;
  }

  /**
   * @param non-empty-array<string> $user_array
   *
   * @throws \Exception
   * @throws ExceptionInterface
   */
  private function importProjectsFromShare(int $limit, array $user_array, int $remix_layout, OutputInterface $output): bool
  {
    if ($limit < 0) {
      $limit = 0;
    }

    $projects_to_download = $limit;
    while ($projects_to_download > 0) {
      $amount = random_int(1, max(1, intval(floor($projects_to_download / 5)) + 1));
      $this->userUploadProjects($amount, $user_array[random_int(0, count($user_array) - 1)], $remix_layout, $output);
      $projects_to_download -= $amount;
    }

    $projects_count = count($this->program_manager->findAll());

    return $projects_count > 0;
  }

  /**
   * @throws ExceptionInterface
   */
  private function userUploadProjects(int $limit, string $username, int $remix_layout, OutputInterface $output): int
  {
    return CommandHelper::executeSymfonyCommand(
      'catrobat:import:share',
      $this->getApplicationOrFail(),
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

  private function randomStudioDescriptionGenerator(): string
  {
    $first = ['A', 'The', 'My', 'Our', 'This'];
    $second = ['great', 'awesome', 'fantastic', 'amazing', 'cool', 'wonderful'];
    $third = ['studio', 'creation', 'place', 'world', 'space', 'spot'];

    return $first[array_rand($first)].' '.$second[array_rand($second)].' '.$third[array_rand($third)];
  }

  private function randomStudioNameGenerator(): string
  {
    $first = ['Creative', 'Innovative', 'Imaginary', 'Dreamy', 'Artistic', 'Visionary'];
    $second = ['Studios', 'Creations', 'Projects', 'World', 'Realm'];

    return $first[array_rand($first)].$second[array_rand($second)];
  }

  /**
   * @param non-empty-array<string> $user_array
   *
   * @throws ExceptionInterface
   * @throws RandomException
   * @throws \JsonException
   */
  private function commentOnProjects(array $program_names, array $user_array, OutputInterface $output): void
  {
    foreach ($program_names as $program_name) {
      $random_comment_amount = random_int(0, 3);
      for ($j = 0; $j <= $random_comment_amount; ++$j) {
        $user_id = array_rand($user_array);
        $parameters = [
          'user' => $user_array[$user_id],
          'program_name' => $program_name,
          'message' => $this->randomCommentGenerator(),
        ];

        $ret = CommandHelper::executeSymfonyCommand('catrobat:comment', $this->getApplicationOrFail(), $parameters, $output);
        if (0 !== $ret) {
          $output->writeln('Comment creation failed for '.json_encode($parameters, JSON_THROW_ON_ERROR).' error code: '.$ret);
        }
      }
    }
  }

  /**
   * @param non-empty-array<string>  $user_array
   * @param non-empty-array<Program> $program_array
   *
   * @throws ExceptionInterface
   * @throws RandomException
   * @throws \JsonException
   */
  private function createStudios(array $user_array, array $program_array, OutputInterface $output): void
  {
    $this->createNamedStudios($user_array, $output);

    $random_studio_amount = random_int(5, 8);
    $i = 0;

    for ($j = 0; $j <= $random_studio_amount; ++$j) {
      $admin_user_id = array_rand($user_array);
      $admin_user = $user_array[$admin_user_id];

      // Generate other studio parameters
      $isPublic = (bool) random_int(0, 1);
      $isEnabled = (bool) random_int(0, 1);
      $allowComments = (bool) random_int(0, 1);
      $numUsers = random_int(2, 5);

      $users = [];
      $status = [];

      $users[] = $admin_user;
      $status[] = $this->getRandomStatus();

      for ($k = 1; $k < $numUsers; ++$k) {
        do {
          $random_user_id = array_rand($user_array);
        } while ($random_user_id === $admin_user_id);

        $users[] = $user_array[$random_user_id];
        $status[] = $this->getRandomStatus();
      }

      $numPrograms = random_int(3, 10);
      $programs = [];
      for ($k = 0; $k < $numPrograms; ++$k) {
        $random_program_id = array_rand($program_array);
        /** @var Program $program */
        $program = $program_array[$random_program_id];
        $program->getUser()->getUserIdentifier();

        foreach ($users as $user) {
          if ($user != $program->getUser()->getUsername()) {
            continue;
          }
          if (!$isPublic) {
            continue;
          }
          $programs[] = $program->getName();
        }
      }

      $parameters = [
        'name' => $this->randomStudioNameGenerator().$i,
        'description' => $this->randomStudioDescriptionGenerator(),
        'admin' => $admin_user,
        'is_public' => $isPublic,
        'is_enabled' => $isEnabled,
        'allow_comments' => $allowComments,
        'users' => $users,
        'projects' => $programs,
        'status' => $status,
      ];

      $ret = CommandHelper::executeSymfonyCommand('catrobat:studio', $this->getApplicationOrFail(), $parameters, $output);
      if (0 !== $ret) {
        $output->writeln('Failed to create studio'.json_encode($parameters, JSON_THROW_ON_ERROR).' error code: '.$ret);
      }

      ++$i;
    }
  }

  /**
   * @param non-empty-array<string> $user_array
   */
  private function createNamedStudios(array $user_array, OutputInterface $output): void
  {
    $output->writeln('Creating named demo studios...');

    /** @var array<array{name: string, description: string, is_public: bool, admin_index: int, member_indices: list<int>, featured: bool}> $studios */
    $studios = [
      [
        'name' => 'Art Studio',
        'description' => 'A creative space for sharing artwork and visual projects',
        'is_public' => true,
        'admin_index' => 0,
        'member_indices' => [1, 2, 3],
        'featured' => true,
      ],
      [
        'name' => 'Game Makers',
        'description' => 'Collaborate on building amazing games together',
        'is_public' => true,
        'admin_index' => 1,
        'member_indices' => [0, 2, 4, 5],
        'featured' => false,
      ],
      [
        'name' => 'Music Lab',
        'description' => 'Experiment with sounds and music projects',
        'is_public' => true,
        'admin_index' => 2,
        'member_indices' => [0, 3],
        'featured' => false,
      ],
      [
        'name' => 'Secret Inventors Club',
        'description' => 'A private studio for invited members only',
        'is_public' => false,
        'admin_index' => 3,
        'member_indices' => [0, 1],
        'featured' => false,
      ],
      [
        'name' => 'Animation Workshop',
        'description' => 'Learn and share animation techniques with the community',
        'is_public' => true,
        'admin_index' => 4,
        'member_indices' => [0, 1, 2, 3, 5],
        'featured' => true,
      ],
    ];

    $created_count = 0;
    foreach ($studios as $config) {
      $admin_index = min($config['admin_index'], count($user_array) - 1);
      $admin_user = $user_array[$admin_index];

      $users = [$admin_user];
      $status = ['pending'];

      foreach ($config['member_indices'] as $member_index) {
        if ($member_index >= count($user_array) || $member_index === $admin_index) {
          continue;
        }
        $users[] = $user_array[$member_index];
        $status[] = 'pending';
      }

      $parameters = [
        'name' => $config['name'],
        'description' => $config['description'],
        'admin' => $admin_user,
        'is_public' => $config['is_public'],
        'is_enabled' => true,
        'allow_comments' => true,
        'users' => $users,
        'projects' => [],
        'status' => $status,
      ];

      $ret = CommandHelper::executeSymfonyCommand('catrobat:studio', $this->getApplicationOrFail(), $parameters, $output);
      if (0 !== $ret) {
        $output->writeln('  Failed to create named studio "'.$config['name'].'"');
        continue;
      }

      if ($config['featured']) {
        $this->featureStudio($config['name'], $output);
      }

      ++$created_count;
    }

    $output->writeln(sprintf('  Created %d named studios', $created_count));
  }

  private function featureStudio(string $studioName, OutputInterface $output): void
  {
    $studioRepo = $this->entity_manager->getRepository(\App\DB\Entity\Studio\Studio::class);
    $studio = $studioRepo->findOneBy(['name' => $studioName]);
    if (null === $studio) {
      $output->writeln('  Could not feature studio "'.$studioName.'": not found');

      return;
    }

    $banner = new FeaturedBanner();
    $banner->setType('studio');
    $banner->setStudio($studio);
    $banner->setTitle($studioName);
    $banner->setActive(true);
    $banner->setPriority(5);
    $banner->setImageType('');
    $banner->setCreatedOn(new \DateTime());
    $this->entity_manager->persist($banner);
    $this->entity_manager->flush();

    $output->writeln('  Featured studio "'.$studioName.'"');
  }

  /**
   * @param Program[] $programs
   */
  private function createFeaturedBanners(array $programs, OutputInterface $output): void
  {
    $output->writeln('Creating featured banners...');
    $count = 0;

    // Create a project banner from the first available project
    if ([] !== $programs) {
      $program = $programs[0];
      $banner = new FeaturedBanner();
      $banner->setType('project');
      $banner->setProgram($program);
      $banner->setTitle($program->getName());
      $banner->setActive(true);
      $banner->setPriority(10);
      $banner->setImageType('');
      $this->entity_manager->persist($banner);
      ++$count;
    }

    // Create a custom link banner
    $banner = new FeaturedBanner();
    $banner->setType('link');
    $banner->setUrl('https://catrobat.org');
    $banner->setTitle('Visit Catrobat');
    $banner->setActive(true);
    $banner->setPriority(3);
    $banner->setImageType('');
    $this->entity_manager->persist($banner);
    ++$count;

    // Create an image-only banner
    $banner = new FeaturedBanner();
    $banner->setType('image');
    $banner->setTitle('Welcome to Catroweb');
    $banner->setActive(true);
    $banner->setPriority(1);
    $banner->setImageType('');
    $this->entity_manager->persist($banner);
    ++$count;

    $this->entity_manager->flush();
    $output->writeln(sprintf('  Created %d featured banners', $count));
  }

  private function getRandomStatus(): string
  {
    /** @var non-empty-array<string> $statuses */
    $statuses = ['pending', 'declined'];

    return $statuses[array_rand($statuses)];
  }

  /**
   * @param Program[]               $programs
   * @param non-empty-array<string> $user_array
   *
   * @throws RandomException
   */
  private function createModerationData(array $programs, array $user_array, OutputInterface $output): void
  {
    $output->writeln('Creating moderation test data...');

    /** @var non-empty-array<string> $project_categories */
    $project_categories = ReportCategory::getValidCategories(ContentType::Project);
    /** @var non-empty-array<string> $comment_categories */
    $comment_categories = ReportCategory::getValidCategories(ContentType::Comment);
    $report_count = 0;

    // Report a subset of projects
    $rand_interval = random_int(4, 6);
    foreach ($programs as $i => $program) {
      if (0 !== $i % $rand_interval) {
        continue;
      }

      $reporter_name = $user_array[array_rand($user_array)];
      $reporter = $this->user_manager->findUserByUsername($reporter_name);
      if (!$reporter instanceof \App\DB\Entity\User\User) {
        continue;
      }
      if ($reporter->getId() === $program->getUser()?->getId()) {
        continue;
      }

      $report = new ContentReport();
      $report->setReporter($reporter);
      $report->setContentType(ContentType::Project->value);
      $report->setContentId($program->getId());
      $report->setCategory($project_categories[array_rand($project_categories)]);
      $report->setNote('This project seems problematic');
      $report->setReporterTrustScore(random_int(10, 30) / 10.0);
      $this->entity_manager->persist($report);
      ++$report_count;
    }

    // Report some comments
    $comments = $this->entity_manager->getRepository(UserComment::class)->findBy([], ['id' => 'ASC'], 5);
    foreach ($comments as $comment) {
      $reporter_name = $user_array[array_rand($user_array)];
      $reporter = $this->user_manager->findUserByUsername($reporter_name);
      if (!$reporter instanceof \App\DB\Entity\User\User) {
        continue;
      }
      if ($reporter->getId() === $comment->getUser()?->getId()) {
        continue;
      }

      $report = new ContentReport();
      $report->setReporter($reporter);
      $report->setContentType(ContentType::Comment->value);
      $report->setContentId((string) $comment->getId());
      $report->setCategory($comment_categories[array_rand($comment_categories)]);
      $report->setReporterTrustScore(random_int(10, 30) / 10.0);
      $this->entity_manager->persist($report);
      ++$report_count;
    }

    // Auto-hide one project and create an appeal
    if (count($programs) > 2) {
      $hidden_project = $programs[1];
      $hidden_project->setAutoHidden(true);

      $owner = $hidden_project->getUser();
      if (null !== $owner) {
        $appeal = new ContentAppeal();
        $appeal->setContentType(ContentType::Project->value);
        $appeal->setContentId($hidden_project->getId());
        $appeal->setAppellant($owner);
        $appeal->setReason('This project was hidden by mistake, it follows all community guidelines.');
        $this->entity_manager->persist($appeal);
      }
    }

    $this->entity_manager->flush();
    $output->writeln(sprintf('  Created %d reports, 1 auto-hidden project with appeal', $report_count));
  }

  /**
   * @throws ExceptionInterface
   * @throws RandomException
   * @throws \JsonException
   */
  private function likeProjects(array $program_names, array $user_array, OutputInterface $output): void
  {
    foreach ($program_names as $program_name) {
      $like_amount = random_int(1, 5);
      for ($i = 0; $i < $like_amount; ++$i) {
        $parameters = [
          'program_name' => $program_name,
          'user_name' => $user_array[($i + $like_amount) % count($user_array)],
        ];
        $ret = CommandHelper::executeSymfonyCommand('catrobat:like', $this->getApplicationOrFail(), $parameters, $output);

        if (0 !== $ret) {
          $output->writeln('Project like creation failed for '.json_encode($parameters, JSON_THROW_ON_ERROR).' error code: '.$ret);
        }
      }
    }
  }

  /**
   * @param non-empty-array<string> $user_array
   *
   * @throws ExceptionInterface
   * @throws RandomException
   * @throws \JsonException
   */
  private function downloadProjects(array $program_names, array $user_array, OutputInterface $output): void
  {
    foreach ($program_names as $program_name) {
      $download_amount = random_int(1, 5);
      for ($i = 0; $i < $download_amount; ++$i) {
        $parameters = [
          'program_name' => $program_name,
          'user_name' => $user_array[array_rand($user_array)],
        ];
        $ret = CommandHelper::executeSymfonyCommand('catrobat:download', $this->getApplicationOrFail(), $parameters, $output);

        if (0 !== $ret) {
          $output->writeln('Project Download creation failed for '.json_encode($parameters, JSON_THROW_ON_ERROR).' error code: '.$ret);
        }
      }
    }
  }

  /**
   * @throws ExceptionInterface
   * @throws RandomException
   * @throws \JsonException
   */
  private function featureProjects(array $program_names, OutputInterface $output): void
  {
    $rand_start = random_int(1, 2);
    $rand_interval = random_int(4, 6);
    $counter = count($program_names);

    for ($i = $rand_start; $i < $counter; $i += $rand_interval) {
      $parameters = [
        'program_name' => $program_names[$i % count($program_names)],
      ];
      $ret = CommandHelper::executeSymfonyCommand('catrobat:feature', $this->getApplicationOrFail(), $parameters, $output);

      if (0 !== $ret) {
        // Might fail because of missing screenshots!
        $output->writeln('Setting project to featured failed for '.json_encode($parameters, JSON_THROW_ON_ERROR).' error code: '.$ret);
      }
    }
  }

  /**
   * @param non-empty-array<string> $user_array
   *
   * @throws ExceptionInterface
   * @throws RandomException
   * @throws \JsonException
   */
  private function followUsers(array $user_array, OutputInterface $output): void
  {
    $counter = count($user_array);
    for ($i = 0; $i < $counter; ++$i) {
      $user_id = $i;
      $follower_id = random_int(0, count($user_array) - 1);

      $parameters = [
        'user_name' => $user_array[$user_id],
        'follower' => $user_array[$follower_id],
      ];
      $ret = CommandHelper::executeSymfonyCommand('catrobat:follow', $this->getApplicationOrFail(), $parameters, $output);

      if (0 !== $ret) {
        $output->writeln('Follow Action failed for '.json_encode($parameters, JSON_THROW_ON_ERROR).' error code: '.$ret);
      }
    }
  }

  /**
   * @throws ExceptionInterface
   * @throws RandomException
   * @throws \JsonException
   */
  private function exampleProject(array $program_names, OutputInterface $output): void
  {
    $rand_start = random_int(1, 2);
    $rand_interval = random_int(4, 6);
    $counter = count($program_names);

    for ($i = $rand_start; $i < $counter; $i += $rand_interval) {
      $parameters = [
        'program_name' => $program_names[$i % count($program_names)],
      ];
      $ret = CommandHelper::executeSymfonyCommand('catrobat:example', $this->getApplicationOrFail(), $parameters, $output);

      if (0 !== $ret) {
        // Might fail because of missing screenshots!
        $output->writeln('Setting project to example failed for '.json_encode($parameters, JSON_THROW_ON_ERROR).' error code: '.$ret);
      }
    }
  }

  /**
   * @throws ExceptionInterface
   * @throws RandomException
   * @throws \JsonException
   */
  private function markNotForKids(array $program_names, OutputInterface $output): void
  {
    foreach ($program_names as $program_name) {
      $mark = random_int(1, 100);
      if ($mark <= 15) {
        $parameters = [
          'program_name' => $program_name,
          'type' => random_int(1, 2),
        ];
        $ret = CommandHelper::executeSymfonyCommand('catrobat:notforkids', $this->getApplicationOrFail(), $parameters, $output);

        if (0 !== $ret) {
          $output->writeln('Marking project not safe for kids failed for '.json_encode($parameters, JSON_THROW_ON_ERROR).' error code: '.$ret);
        }
      }
    }
  }

  private function addStatistics(): void
  {
    $statistic = $this->statistic_repository->find(1);
    if (!$statistic instanceof Statistic) {
      $statistic = new Statistic();
    }
    $statistic->setProjects('13461234621');
    $statistic->setUsers('345423543');
    $this->entity_manager->persist($statistic);
    $this->entity_manager->flush();
  }

  private function getApplicationOrFail(): Application
  {
    $application = $this->getApplication();
    if (null === $application) {
      throw new \RuntimeException('Application not available');
    }

    return $application;
  }
}
