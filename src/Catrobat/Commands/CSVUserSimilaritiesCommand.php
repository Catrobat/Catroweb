<?php

namespace App\Catrobat\Commands;

use App\Entity\ProgramLike;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRemixRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;


/**
 * Class CSVUserSimilaritiesCommand
 * @package App\Catrobat\Commands
 */
class CSVUserSimilaritiesCommand extends ContainerAwareCommand
{
  /**
   * @var ProgramRemixRepository
   */
  private $program_remix_repository;

  /**
   * @var ProgramLikeRepository
   */
  private $program_like_repository;

  /**
   * @var EntityManager
   */
  private $entity_manager;

  /**
   * @var string
   */
  private $app_root_dir;


  /**
   * CSVUserSimilaritiesCommand constructor.
   *
   * @param ProgramRemixRepository $program_remix_repository
   * @param ProgramLikeRepository  $program_like_repository
   * @param EntityManager          $entity_manager
   * @param                        $app_root_dir
   */
  public function __construct(ProgramRemixRepository $program_remix_repository,
                              ProgramLikeRepository $program_like_repository,
                                 EntityManager $entity_manager, $app_root_dir)
  {
    parent::__construct();
    $this->program_remix_repository = $program_remix_repository;
    $this->program_like_repository = $program_like_repository;
    $this->entity_manager = $entity_manager;
    $this->app_root_dir = $app_root_dir;
  }

  /**
   *
   */
  protected function configure()
  {
    date_default_timezone_set('Europe/Berlin');
    $this->setName('catrobat:recommender:export')->setDescription('Export command');
  }

  /**
   * @param InputInterface  $input
   * @param OutputInterface $output
   *
   * @return int|void|null
   * @throws \Doctrine\DBAL\DBALException
   */
  protected function execute(InputInterface $input, OutputInterface $output)
  {
    /**
     * @var $likes_of_users array
     * @var $likes_of_user ProgramLike
     */

    @unlink($this->app_root_dir . '/../data_remixes');
    @unlink($this->app_root_dir . '/../data_likes');

    $statement = $this->entity_manager->getConnection()
      ->prepare("SELECT MAX(id) as id_of_last_user FROM fos_user");
    $statement->execute();
    $id_of_last_user = intval($statement->fetch()['id_of_last_user']);
    echo PHP_EOL . 'Last ID: ' . $id_of_last_user . PHP_EOL;

    $output_string = '';
    for ($user_id = 1; $user_id <= $id_of_last_user; $user_id++)
    {
      $remixes_of_user = $this->program_remix_repository->getDirectParentRelationDataOfUser($user_id);
      foreach ($remixes_of_user as $remix_of_user)
      {
        $output_string .= $user_id . ';' . $remix_of_user['ancestor_id'] . ';' . $remix_of_user['descendant_id'] . PHP_EOL;
      }
    }
    file_put_contents($this->app_root_dir . '/../data_remixes', $output_string);

    $output_string = '';
    $likes_of_all_users = $this->program_like_repository->findAll();
    foreach ($likes_of_all_users as $likes_of_user)
    {
      $user_id = $likes_of_user->getUserId();
      $program_id = $likes_of_user->getProgramId();
      $output_string .= $user_id . ';' . $program_id . PHP_EOL;
    }
    file_put_contents($this->app_root_dir . '/../data_likes', $output_string);
  }
}
