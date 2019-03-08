<?php

namespace App\Catrobat\RecommenderSystem;

use App\Entity\ProgramLike;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRemixBackwardRepository;
use App\Entity\ProgramRemixRelation;
use App\Repository\ProgramRemixRepository;
use App\Entity\User;
use App\Entity\UserManager;
use App\Entity\UserLikeSimilarityRelation;
use App\Repository\UserLikeSimilarityRelationRepository;
use App\Repository\UserRemixSimilarityRelationRepository;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Helper\ProgressBar;


/**
 * Class RecommenderManager
 * @package App\Catrobat\RecommenderSystem
 */
class RecommenderManager
{
  const RECOMMENDER_LOCK_FILE_NAME = 'CatrobatRecommender.lock';
  /**
   * @var EntityManager The entity manager.
   */
  private $entity_manager;

  /**
   * @var UserManager
   */
  private $user_manager;

  /**
   * @var UserLikeSimilarityRelationRepository
   */
  private $user_like_similarity_relation_repository;

  /**
   * @var UserRemixSimilarityRelationRepository
   */
  private $user_remix_similarity_relation_repository;

  /**
   * @var ProgramLikeRepository
   */
  private $program_like_repository;

  /**
   * @var ProgramRemixRepository
   */
  private $program_remix_repository;

  /**
   * @var ProgramRemixBackwardRepository
   */
  private $program_remix_backward_repository;


  /**
   * RecommenderManager constructor.
   *
   * @param EntityManager                         $entity_manager
   * @param UserManager                           $user_manager
   * @param UserLikeSimilarityRelationRepository  $user_like_similarity_relation_repository
   * @param UserRemixSimilarityRelationRepository $user_remix_similarity_relation_repository
   * @param ProgramLikeRepository                 $program_like_repository
   * @param ProgramRemixRepository                $program_remix_repository
   * @param ProgramRemixBackwardRepository        $program_remix_backward_repository
   */
  public function __construct(EntityManager $entity_manager, UserManager $user_manager,
                              UserLikeSimilarityRelationRepository $user_like_similarity_relation_repository,
                              UserRemixSimilarityRelationRepository $user_remix_similarity_relation_repository,
                              ProgramLikeRepository $program_like_repository,
                              ProgramRemixRepository $program_remix_repository,
                              ProgramRemixBackwardRepository $program_remix_backward_repository)
  {
    $this->entity_manager = $entity_manager;
    $this->user_manager = $user_manager;
    $this->user_like_similarity_relation_repository = $user_like_similarity_relation_repository;
    $this->user_remix_similarity_relation_repository = $user_remix_similarity_relation_repository;
    $this->program_like_repository = $program_like_repository;
    $this->program_remix_repository = $program_remix_repository;
    $this->program_remix_backward_repository = $program_remix_backward_repository;
  }

  /**
   * @param $array1
   * @param $array2
   */
  private function imitateMerge(&$array1, &$array2)
  {
    foreach ($array2 as $i)
    {
      $array1[] = $i;
    }
  }

  /**
   *
   */
  public function removeAllUserLikeSimilarityRelations()
  {
    $this->user_like_similarity_relation_repository->removeAllUserRelations();
  }

  /**
   *
   */
  public function removeAllUserRemixSimilarityRelations()
  {
    $this->user_remix_similarity_relation_repository->removeAllUserRelations();
  }

  /**
   *
   * Collaborative Filtering by using Jaccard Distance
   * As in this case we have to deal with TRUE/FALSE ratings (i.e. user liked the program OR has not seen/liked it yet)
   * the Jaccard distance is used to measure the similarity between two users.
   *
   *   n ... total number of users that have liked at least one program
   *   m ... total number of liked programs
   *
   * @see            : http://infolab.stanford.edu/~ullman/mmds/ch9.pdf (section 9.3)
   * @time_complexity: O(n^2 * m)
   *
   * @param ProgressBar $progress_bar
   *
   * @throws \Doctrine\ORM\ORMException
   * @throws \Doctrine\ORM\OptimisticLockException
   */
  public function computeUserLikeSimilarities($progress_bar = null)
  {
    $users = $this->user_manager->findAll();
    $rated_users = array_unique(array_filter($users, function ($user) {
      /**
       * @var $user User
       */
      return (count($this->program_like_repository->findBy(['user_id' => $user->getId()])) > 0);
    }));

    $already_added_relations = [];

    /**
     * @var $first_user User
     * @var $second_user User
     */
    foreach ($rated_users as $first_user)
    {
      if ($progress_bar != null)
      {
        $progress_bar->setMessage('Computing like similarity of user (#' . $first_user->getId() . ')');
      }

      $first_user_likes = $this->program_like_repository->findBy(['user_id' => $first_user->getId()]);
      $ids_of_programs_liked_by_first_user = array_map(function ($like) {
        /**
         * @var $like ProgramLike
         */
        return $like->getProgramId();
      }, $first_user_likes);

      foreach ($rated_users as $second_user)
      {
        $key = $first_user->getId() . '_' . $second_user->getId();
        $reverse_key = $second_user->getId() . '_' . $first_user->getId();

        if (($first_user->getId() == $second_user->getId()) || in_array($key, $already_added_relations)
          || in_array($reverse_key, $already_added_relations)
        )
        {
          continue;
        }

        $already_added_relations[] = $key;
        $second_user_likes = $this->program_like_repository->findBy(['user_id' => $second_user->getId()]);
        $ids_of_programs_liked_by_second_user = array_map(function ($like) {
          /**
           * @var $like ProgramLike
           */
          return $like->getProgramId();
        }, $second_user_likes);

        $ids_of_same_programs_liked_by_both = array_unique(array_intersect($ids_of_programs_liked_by_first_user, $ids_of_programs_liked_by_second_user));
        // make copy of array -> merge with empty array is fast shortcut!
        $temp = array_merge([], $ids_of_programs_liked_by_first_user);
        // this imitate merge is way more faster than using array_merge() with huge arrays!
        // -> this has a significant impact on performance here!
        $this->imitateMerge($temp, $ids_of_programs_liked_by_second_user);
        $ids_of_all_programs_liked_by_any_of_both = array_unique($temp);

        $number_of_same_programs_liked_by_both = count($ids_of_same_programs_liked_by_both);
        $number_of_all_programs_liked_by_any_of_both = count($ids_of_all_programs_liked_by_any_of_both);

        if ($number_of_same_programs_liked_by_both == 0)
        {
          continue;
        }

        $jaccard_similarity = floatval($number_of_same_programs_liked_by_both) / floatval($number_of_all_programs_liked_by_any_of_both);
        $similarity_relation = new UserLikeSimilarityRelation($first_user, $second_user, $jaccard_similarity);
        $this->entity_manager->persist($similarity_relation);
        $this->entity_manager->flush($similarity_relation);
      }

      if ($progress_bar != null)
      {
        $progress_bar->clear();
        $progress_bar->advance();
        $progress_bar->display();
      }
    }
  }

  /**
   * @param $user
   * @param $flavor
   *
   * @return array
   */
  public function recommendProgramsOfLikeSimilarUsers($user, $flavor)
  {
    /**
     * @var $user User
     * @var $r UserLikeSimilarityRelation
     */
    // NOTE: this parameter should/can be increased after A/B testing has ended!
    //       -> meaningful values for this simple algorithm would be between 4-6
    // NOTE: If you modify this parameter, some tests will intentionally fail as they rely on the value of this parameter!
    //       -> Don't forget to update them as well.
    $min_num_of_likes_required_to_allow_recommendations = 1;

    $all_likes_of_user = $this->program_like_repository->findBy(['user_id' => $user->getId()]);

    if (count($all_likes_of_user) < $min_num_of_likes_required_to_allow_recommendations)
    {
      return [];
    }

    $user_similarity_relations = $this->user_like_similarity_relation_repository->getRelationsOfSimilarUsers($user);
    $similar_user_similarity_mapping = [];

    foreach ($user_similarity_relations as $r)
    {
      $id_of_similar_user = ($r->getFirstUserId() != $user->getId()) ? $r->getFirstUserId() : $r->getSecondUserId();
      $similar_user_similarity_mapping[$id_of_similar_user] = $r->getSimilarity();
    }

    $ids_of_similar_users = array_keys($similar_user_similarity_mapping);
    $excluded_ids_of_liked_programs = array_unique(array_map(function ($like) {
      /**
       * @var $like ProgramLike
       */
      return $like->getProgramId();
    }, $all_likes_of_user));

    $differing_likes = $this->program_like_repository->getLikesOfUsers(
      $ids_of_similar_users, $user->getId(), $excluded_ids_of_liked_programs, $flavor);

    $recommendation_weights = [];
    $programs_liked_by_others = [];
    foreach ($differing_likes as $differing_like)
    {
      /**
       * @var $differing_like ProgramLike
       */
      $key = $differing_like->getProgramId();
      assert(!in_array($key, $excluded_ids_of_liked_programs));

      if (!array_key_exists($key, $recommendation_weights))
      {
        $recommendation_weights[$key] = 0.0;
        $programs_liked_by_others[$key] = $differing_like->getProgram();
      }

      $recommendation_weights[$key] += $similar_user_similarity_mapping[$differing_like->getUserId()];
    }

    arsort($recommendation_weights);

    return array_map(function ($program_id) use ($programs_liked_by_others) {
      return $programs_liked_by_others[$program_id];
    }, array_keys($recommendation_weights));
  }

  /**
   *
   * Collaborative Filtering by using Jaccard Distance
   * As in this case we have to deal with TRUE/FALSE values (i.e. user remixed the program OR not yet)
   * the Jaccard distance is used to measure the similarity between two users.
   *
   *   n ... total number of users that have remixed at least one program
   *   m ... total number of remixed programs
   *
   * @see            : http://infolab.stanford.edu/~ullman/mmds/ch9.pdf (section 9.3)
   * @time_complexity: O(n^2 * m)
   *
   *
   * @param ProgressBar $progress_bar
   *
   * @throws \Doctrine\DBAL\DBALException
   */
  public function computeUserRemixSimilarities($progress_bar = null)
  {
    // TODO: consider backward & scratch relations too... (but very low priority as they won't affect the recommendations significantly!)
    $statement = $this->entity_manager->getConnection()->prepare("SELECT MAX(id) as id_of_last_user FROM fos_user");
    $statement->execute();
    $id_of_last_user = intval($statement->fetch()['id_of_last_user']);
    $user_remix_relations = [];

    for ($user_id = 1; $user_id <= $id_of_last_user; $user_id++)
    {
      if ($progress_bar != null)
      {
        $progress_bar->setMessage('Fetching remix parents of user (#' . $user_id . ')');
        $progress_bar->clear();
        $progress_bar->advance();
        $progress_bar->display();
      }
      $remixes_of_user = $this->program_remix_repository->getDirectParentRelationDataOfUser($user_id);
      if (count($remixes_of_user) > 0)
      {
        $user_remix_relations[$user_id] = $remixes_of_user;
      }
    }

    $total_number_of_remixed_users = count($user_remix_relations);
    $already_added_relations = [];
    $user_counter = 0;

    foreach ($user_remix_relations as $first_user_id => $first_user_remix_relations)
    {
      $ids_of_programs_remixed_by_first_user = array_unique(array_map(function ($data) {
        return $data['ancestor_id'];
      }, $first_user_remix_relations));
      ++$user_counter;

      foreach ($user_remix_relations as $second_user_id => $second_user_remix_relations)
      {
        if ($progress_bar != null)
        {
          $progress_bar->setMessage('(' . $user_counter . '/' . $total_number_of_remixed_users
            . ') - Computing remix similarity between user #' . $first_user_id . ' and user #' . $second_user_id);
        }

        $key = $first_user_id . '_' . $second_user_id;
        $reverse_key = $second_user_id . '_' . $first_user_id;

        if (($first_user_id == $second_user_id) || in_array($key, $already_added_relations)
          || in_array($reverse_key, $already_added_relations)
        )
        {
          continue;
        }

        $already_added_relations[] = $key;
        $ids_of_programs_remixed_by_second_user = array_unique(array_map(function ($data) {
          return $data['ancestor_id'];
        }, $second_user_remix_relations));

        $ids_of_same_programs_remixed_by_both = array_unique(array_intersect($ids_of_programs_remixed_by_first_user, $ids_of_programs_remixed_by_second_user));
        // make copy of array -> merge with empty array is fast shortcut!
        $temp = array_merge([], $ids_of_programs_remixed_by_first_user);
        // this imitate merge is way more faster than using array_merge() with huge arrays!
        // -> this has a significant impact on performance here!
        $this->imitateMerge($temp, $ids_of_programs_remixed_by_second_user);
        $ids_of_all_programs_remixed_by_any_of_both = array_unique($temp);

        $number_of_same_programs_remixed_by_both = count($ids_of_same_programs_remixed_by_both);
        $number_of_all_programs_remixed_by_any_of_both = count($ids_of_all_programs_remixed_by_any_of_both);

        if ($number_of_same_programs_remixed_by_both == 0)
        {
          continue;
        }

        $jaccard_similarity = floatval($number_of_same_programs_remixed_by_both) / floatval($number_of_all_programs_remixed_by_any_of_both);
        if ($jaccard_similarity >= 0.01)
        {
          $this->user_remix_similarity_relation_repository->insertRelation($first_user_id, $second_user_id, $jaccard_similarity);
        }

        if ($progress_bar != null)
        {
          $progress_bar->clear();
          $progress_bar->advance();
          $progress_bar->display();
        }
      }
    }
  }

  /**
   * @param $user
   * @param $flavor
   *
   * @return array
   */
  public function recommendProgramsOfRemixSimilarUsers($user, $flavor)
  {
    /**
     * @var $user User
     * @var $r UserLikeSimilarityRelation
     * @var $relation_of_differing_parent ProgramRemixRelation
     */
    // NOTE: this parameter should/can be increased after A/B testing has ended!
    //       -> meaningful values for this simple algorithm would be between 3-4
    // NOTE: If you modify this parameter, some tests will intentionally fail as they rely on the value of this parameter!
    //       -> Don't forget to update them as well.
    $min_num_of_remixes_required_to_allow_recommendations = 1;

    $parent_relations_of_all_remixed_programs_of_user = $this
      ->program_remix_repository
      ->getDirectParentRelationDataOfUser($user->getId());

    if (count($parent_relations_of_all_remixed_programs_of_user) < $min_num_of_remixes_required_to_allow_recommendations)
    {
      return [];
    }

    $user_similarity_relations = $this->user_remix_similarity_relation_repository->getRelationsOfSimilarUsers($user);
    $similar_user_similarity_mapping = [];

    foreach ($user_similarity_relations as $r)
    {
      $id_of_similar_user = ($r->getFirstUserId() != $user->getId()) ? $r->getFirstUserId() : $r->getSecondUserId();
      $similar_user_similarity_mapping[$id_of_similar_user] = $r->getSimilarity();
    }

    $ids_of_similar_users = array_keys($similar_user_similarity_mapping);
    $excluded_ids_of_remixed_programs = array_unique(array_map(function ($data) {
      return $data['ancestor_id'];
    }, $parent_relations_of_all_remixed_programs_of_user));

    $relations_of_differing_parents = $this
      ->program_remix_repository
      ->getDirectParentRelationsOfUsersRemixes(
        $ids_of_similar_users, $user->getId(), $excluded_ids_of_remixed_programs, $flavor);

    $recommendation_weights = [];
    $programs_remixed_by_others = [];
    foreach ($relations_of_differing_parents as $relation_of_differing_parent)
    {
      $key = $relation_of_differing_parent->getAncestorId();
      assert(!in_array($key, $excluded_ids_of_remixed_programs));

      if (!array_key_exists($key, $recommendation_weights))
      {
        $recommendation_weights[$key] = 0.0;
        $programs_remixed_by_others[$key] = $relation_of_differing_parent->getAncestor();
      }

      $id_of_corresponding_similar_user = $relation_of_differing_parent->getDescendant()->getUser()->getId();
      $recommendation_weights[$key] += $similar_user_similarity_mapping[$id_of_corresponding_similar_user];
    }

    arsort($recommendation_weights);

    return array_map(function ($program_id) use ($programs_remixed_by_others) {
      return $programs_remixed_by_others[$program_id];
    }, array_keys($recommendation_weights));
  }
}
