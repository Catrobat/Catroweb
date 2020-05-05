<?php

namespace App\Commands;

use App\Entity\User;
use App\Entity\UserManager;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRemixRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class CSVUserSimilaritiesCommand extends Command
{
  protected static $defaultName = 'catrobat:recommender:export';
  private ProgramRemixRepository $program_remix_repository;

  private ProgramLikeRepository $program_like_repository;

  private EntityManagerInterface $entity_manager;

  private UserManager $user_manager;

  private string $app_root_dir;

  public function __construct(ProgramRemixRepository $program_remix_repository,
                              ProgramLikeRepository $program_like_repository,
                              EntityManagerInterface $entity_manager,
                              UserManager $user_manager,
                              ParameterBagInterface $parameter_bag)
  {
    parent::__construct();
    $this->program_remix_repository = $program_remix_repository;
    $this->program_like_repository = $program_like_repository;
    $this->entity_manager = $entity_manager;
    $this->user_manager = $user_manager;
    $this->app_root_dir = $parameter_bag->get('kernel.project_dir');
  }

  protected function configure(): void
  {
    date_default_timezone_set('Europe/Berlin');
    $this->setName('catrobat:recommender:export')->setDescription('Export command');
  }

  protected function execute(InputInterface $input, OutputInterface $output): int
  {
    @unlink($this->app_root_dir.'/../data_remixes');
    @unlink($this->app_root_dir.'/../data_likes');

    $users = $this->user_manager->findAll();

    echo PHP_EOL.'Number of users: '.count($users).PHP_EOL;

    $output_string = '';

    /** @var User $user */
    foreach ($users as $user)
    {
      $user_id = $user->getId();

      $remixes_of_user = $this->program_remix_repository->getDirectParentRelationDataOfUser($user_id);

      /** @var array $remix_of_user */
      foreach ($remixes_of_user as $remix_of_user)
      {
        $output_string .= $user_id.';'.$remix_of_user['ancestor_id'].';'.$remix_of_user['descendant_id'].PHP_EOL;
      }
    }

    file_put_contents($this->app_root_dir.'/../data_remixes', $output_string);

    echo PHP_EOL.'Content written to '.$this->app_root_dir.'/../data_remixes';

    $output_string = '';
    $likes_of_all_users = $this->program_like_repository->findAll();
    foreach ($likes_of_all_users as $likes_of_user)
    {
      $user_id = $likes_of_user->getUserId();
      $program_id = $likes_of_user->getProgramId();
      $output_string .= $user_id.';'.$program_id.PHP_EOL;
    }

    file_put_contents($this->app_root_dir.'/../data_likes', $output_string);

    echo PHP_EOL.'Content written to '.$this->app_root_dir.'/../data_likes';

    return 0;
  }
}
