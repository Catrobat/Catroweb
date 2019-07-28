<?php

namespace App\Catrobat\RecommenderSystem;

use App\Catrobat\Requests\AppRequest;
use App\Entity\Program;
use App\Entity\ProgramLike;
use App\Entity\User;
use App\Entity\UserLikeSimilarityRelation;
use App\Entity\UserManager;
use App\Repository\ProgramLikeRepository;
use App\Repository\ProgramRemixBackwardRepository;
use App\Repository\ProgramRemixRepository;
use App\Repository\ProgramRepository;
use App\Repository\UserLikeSimilarityRelationRepository;
use App\Repository\UserRemixSimilarityRelationRepository;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
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
   * @var ProgramRepository
   */
  private $program_repository;

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
   * @var AppRequest
   */
  protected $app_request;


  /**
   * RecommenderManager constructor.
   *
   * @param EntityManager                         $entity_manager
   * @param UserManager                           $user_manager
   * @param UserLikeSimilarityRelationRepository  $user_like_similarity_relation_repository
   * @param UserRemixSimilarityRelationRepository $user_remix_similarity_relation_repository
   * @param ProgramRepository                     $program_repository
   * @param ProgramLikeRepository                 $program_like_repository
   * @param ProgramRemixRepository                $program_remix_repository
   * @param ProgramRemixBackwardRepository        $program_remix_backward_repository
   * @param AppRequest                            $app_request
   */
  public function __construct(EntityManager $entity_manager, UserManager $user_manager,
                              UserLikeSimilarityRelationRepository $user_like_similarity_relation_repository,
                              UserRemixSimilarityRelationRepository $user_remix_similarity_relation_repository,
                              ProgramRepository $program_repository,
                              ProgramLikeRepository $program_like_repository,
                              ProgramRemixRepository $program_remix_repository,
                              ProgramRemixBackwardRepository $program_remix_backward_repository,
                              AppRequest $app_request)
  {
    $this->entity_manager = $entity_manager;
    $this->user_manager = $user_manager;
    $this->user_like_similarity_relation_repository = $user_like_similarity_relation_repository;
    $this->user_remix_similarity_relation_repository = $user_remix_similarity_relation_repository;
    $this->program_repository = $program_repository;
    $this->program_like_repository = $program_like_repository;
    $this->program_remix_repository = $program_remix_repository;
    $this->program_remix_backward_repository = $program_remix_backward_repository;
    $this->app_request = $app_request;
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
   * As in this case we have to deal with TRUE/FALSE ratings
   * (i.e. user liked the program OR has not seen/liked it yet)
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
   * @throws ORMException
   * @throws OptimisticLockException
   */
  public function computeUserLikeSimilarities($progress_bar = null)
  {
    $users = $this->user_manager->findAll();
    $rated_users = array_unique(array_filter($users, function (User $user) {
      return (count($this->program_like_repository->findBy(['user_id' => $user->getId()])) > 0);
    }));

    $already_added_relations = [];

    /**
     * @var $first_user  User
     * @var $second_user User
     */
    foreach ($rated_users as $first_user)
    {
      if ($progress_bar !== null)
      {
        $progress_bar->setMessage('Computing like similarity of user (#' . $first_user->getId() . ')');
      }

      $first_user_likes = $this->program_like_repository->findBy(['user_id' => $first_user->getId()]);
      $ids_of_programs_liked_by_first_user = array_map(function (ProgramLike $like) {
        return $like->getProgramId();
      }, $first_user_likes);

      foreach ($rated_users as $second_user)
      {
        $key = $first_user->getId() . '_' . $second_user->getId();
        $reverse_key = $second_user->getId() . '_' . $first_user->getId();

        if (($first_user->getId() === $second_user->getId()) || in_array($key, $already_added_relations)
          || in_array($reverse_key, $already_added_relations)
        )
        {
          continue;
        }

        $already_added_relations[] = $key;
        $second_user_likes = $this->program_like_repository->findBy(['user_id' => $second_user->getId()]);
        $ids_of_programs_liked_by_second_user = array_map(function (ProgramLike $like) {
          return $like->getProgramId();
        }, $second_user_likes);

        $ids_of_same_programs_liked_by_both = array_unique(
          array_intersect(
            $ids_of_programs_liked_by_first_user, $ids_of_programs_liked_by_second_user
          )
        );
        // make copy of array -> merge with empty array is fast shortcut!
        $temp = array_merge([], $ids_of_programs_liked_by_first_user);
        // this imitate merge is way more faster than using array_merge() with huge arrays!
        // -> this has a significant impact on performance here!
        $this->imitateMerge($temp, $ids_of_programs_liked_by_second_user);
        $ids_of_all_programs_liked_by_any_of_both = array_unique($temp);

        $number_of_same_programs_liked_by_both = count($ids_of_same_programs_liked_by_both);
        $number_of_all_programs_liked_by_any_of_both = count($ids_of_all_programs_liked_by_any_of_both);

        if ($number_of_same_programs_liked_by_both === 0)
        {
          continue;
        }

        $jaccard_similarity = floatval($number_of_same_programs_liked_by_both) /
          floatval($number_of_all_programs_liked_by_any_of_both);
        $similarity_relation = new UserLikeSimilarityRelation($first_user, $second_user, $jaccard_similarity);
        $this->entity_manager->persist($similarity_relation);
        $this->entity_manager->flush($similarity_relation);
      }

      if ($progress_bar !== null)
      {
        $progress_bar->clear();
        $progress_bar->advance();
        $progress_bar->display();
      }
    }
  }


  /**
   * This function, which is used for non-personalized recommendations for guest users, recommends
   * the most liked programs. However, programs which are featured high in the list of most downloaded
   * programs are ranked further back, so that more different programs are shown on the homepage
   * (otherwise it is likely that there are many duplicates in the lists of most downloaded / most
   * viewed programs and recommended programs).
   *
   * @param $flavor
   *
   * @return Program[]
   */
  public function recommendHomepageProgramsForGuests($flavor)
  {
    $most_liked_programs =
      $this->program_repository->getMostLikedPrograms(
        $this->app_request->isDebugBuildRequest(), $flavor
      );
    $programs_total_likes = [];
    foreach ($most_liked_programs as $most_liked_program)
    {
      $program_id = $most_liked_program->getId();
      $programs_total_likes[$program_id] = $this->program_like_repository->totalLikeCount($program_id);
    }

    $most_downloaded_programs =
      $this->program_repository->getMostDownloadedPrograms(
        $this->app_request->isDebugBuildRequest(), $flavor, 75
      );
    $ids_of_most_downloaded_programs = array_map(function (Program $program) {
      return $program->getId();
    }, $most_downloaded_programs);


    foreach ($programs_total_likes as $program_id => $number_of_likes)
    {
      $rank_in_top_downloads = array_search($program_id, $ids_of_most_downloaded_programs);
      if ($rank_in_top_downloads !== false)
      {
        $programs_total_likes[$program_id] = $number_of_likes * cos(deg2rad(70 - $rank_in_top_downloads * 1.5))**2;
      }
    }

    arsort($programs_total_likes);

    $recommendation_list = [];
    foreach ($programs_total_likes as $program_id => $number_of_likes)
    {
      $program = $this->program_repository->find($program_id);
      $recommendation_list[] = $program;
    }

    return ProgramRepository::filterVisiblePrograms(
      $recommendation_list, $this->app_request->isDebugBuildRequest()
    );
  }

  /*
   * Three different algorithms for recommending programs on the homepage based on likes
   * follow. They are going to be compared as part of a master's thesis. The general aim
   * is to recommend more different programs.
   */

  /**
   * Algorithm 1 is the baseline algorithm, former "recommendProgramsOfLikeSimilarUsers"
   * (only the function name has been changed here)
   *
   * @param User $user
   * @param      $flavor
   *
   * @return Program[]
   */
  public function recommendHomepageProgramsAlgorithmOne($user, $flavor)
  {
    // NOTE: this parameter should/can be increased after A/B testing has ended!
    //       -> meaningful values for this simple algorithm would be between 4-6
    // NOTE: If you modify this parameter, some tests will intentionally fail
    //       as they rely on the value of this parameter!
    //       -> Don't forget to update them as well.
    $min_num_of_likes_required_to_allow_recommendations = 1;

    $all_likes_of_user = $this->program_like_repository->findBy(['user_id' => $user->getId()]);

    if (count($all_likes_of_user) < $min_num_of_likes_required_to_allow_recommendations)
    {
      return [];
    }

    $user_similarity_relations =
      $this->user_like_similarity_relation_repository->getRelationsOfSimilarUsers($user);
    $similar_user_similarity_mapping = [];

    foreach ($user_similarity_relations as $relation)
    {
      $id_of_similar_user = ($relation->getFirstUserId() !== $user->getId()) ?
        $relation->getFirstUserId() : $relation->getSecondUserId();
      $similar_user_similarity_mapping[$id_of_similar_user] = $relation->getSimilarity();
    }

    $ids_of_similar_users = array_keys($similar_user_similarity_mapping);
    $excluded_ids_of_liked_programs = array_unique(array_map(function (ProgramLike $like) {
      return $like->getProgramId();
    }, $all_likes_of_user));

    $differing_likes = $this->program_like_repository->getLikesOfUsers(
      $ids_of_similar_users, $user->getId(), $excluded_ids_of_liked_programs, $flavor);

    $recommendation_weights = [];
    $programs_liked_by_others = [];
    foreach ($differing_likes as $differing_like)
    {
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

    // $recommendation_weights only holds the program ids and total weights. In order to
    // return an array with elements of the program entity $programs_liked_by_others is
    // used.
    $programs = array_map(function ($program_id) use ($programs_liked_by_others) {
      return $programs_liked_by_others[$program_id];
    }, array_keys($recommendation_weights));

    return ProgramRepository::filterVisiblePrograms(
      $programs, $this->app_request->isDebugBuildRequest()
    );
  }

  /**
   * Algorithm 2 wants to increase the diversity of recommended programs. The approach is
   * decreasing the weights of the top 75 most downloaded programs.
   *
   * @param User $user
   * @param      $flavor
   *
   * @return Program[]
   */
  public function recommendHomepageProgramsAlgorithmTwo($user, $flavor)
  {
    // NOTE: this parameter should/can be increased after A/B testing has ended!
    //       -> meaningful values for this simple algorithm would be between 4-6
    // NOTE: If you modify this parameter, some tests will intentionally fail
    //       as they rely on the value of this parameter!
    //       -> Don't forget to update them as well.
    $min_num_of_likes_required_to_allow_recommendations = 1;

    $all_likes_of_user = $this->program_like_repository->findBy(['user_id' => $user->getId()]);

    if (count($all_likes_of_user) < $min_num_of_likes_required_to_allow_recommendations)
    {
      return [];
    }

    $user_similarity_relations =
      $this->user_like_similarity_relation_repository->getRelationsOfSimilarUsers($user);
    $similar_user_similarity_mapping = [];

    foreach ($user_similarity_relations as $relation)
    {
      $id_of_similar_user = ($relation->getFirstUserId() !== $user->getId()) ?
        $relation->getFirstUserId() : $relation->getSecondUserId();
      $similar_user_similarity_mapping[$id_of_similar_user] = $relation->getSimilarity();
    }

    $ids_of_similar_users = array_keys($similar_user_similarity_mapping);
    $excluded_ids_of_liked_programs = array_unique(array_map(function (ProgramLike $like) {
      return $like->getProgramId();
    }, $all_likes_of_user));

    $differing_likes = $this->program_like_repository->getLikesOfUsers(
      $ids_of_similar_users, $user->getId(), $excluded_ids_of_liked_programs, $flavor);

    $recommendation_weights = [];
    $programs_liked_by_others = [];
    foreach ($differing_likes as $differing_like)
    {
      $key = $differing_like->getProgramId();
      assert(!in_array($key, $excluded_ids_of_liked_programs));

      if (!array_key_exists($key, $recommendation_weights))
      {
        $recommendation_weights[$key] = 0.0;
        $programs_liked_by_others[$key] = $differing_like->getProgram();
      }

      $recommendation_weights[$key] += $similar_user_similarity_mapping[$differing_like->getUserId()];
    }

    /*
     * In order to generate more diverse recommendations, the weights of programs that are
     * ranked within the top 75 most downloaded programs are reduced. The used
     * mathematical function has been chosen because it consistently reduces the weight
     * decrease from rank to rank, fast in the beginning, then slowing down.
     */
    $most_downloaded_programs = $this->program_repository->getMostDownloadedPrograms(
      $this->app_request->isDebugBuildRequest(), $flavor, 75
    );
    $ids_of_most_downloaded_programs = array_map(function (Program $program) {
      return $program->getId();
    }, $most_downloaded_programs);

    foreach ($recommendation_weights as $key => $weight)
    {
      $rank_in_top_downloads = array_search($key, $ids_of_most_downloaded_programs);
      if ($rank_in_top_downloads !== false)
      {
        $recommendation_weights[$key] = $weight * cos(deg2rad(75 - $rank_in_top_downloads));
      }
    }

    arsort($recommendation_weights);

    // $recommendation_weights only holds the program ids and total weights. In order to
    // return an array with elements of the program entity $programs_liked_by_others is
    // used.
    $programs = array_map(function ($program_id) use ($programs_liked_by_others) {
      return $programs_liked_by_others[$program_id];
    }, array_keys($recommendation_weights));

    return ProgramRepository::filterVisiblePrograms(
      $programs, $this->app_request->isDebugBuildRequest()
    );
  }

  /**
   * Algorithm 3 wants to increase the diversity of recommended programs. The approach is
   * re-ranking the recommended items.
   *
   * @param User $user
   * @param      $flavor
   *
   * @return Program[]
   */
  public function recommendHomepageProgramsAlgorithmThree($user, $flavor)
  {
    // NOTE: this parameter should/can be increased after A/B testing has ended!
    //       -> meaningful values for this simple algorithm would be between 4-6
    // NOTE: If you modify this parameter, some tests will intentionally fail
    //       as they rely on the value of this parameter!
    //       -> Don't forget to update them as well.
    $min_num_of_likes_required_to_allow_recommendations = 1;

    $all_likes_of_user = $this->program_like_repository->findBy(['user_id' => $user->getId()]);

    if (count($all_likes_of_user) < $min_num_of_likes_required_to_allow_recommendations)
    {
      return [];
    }

    $user_similarity_relations = $this->user_like_similarity_relation_repository->getRelationsOfSimilarUsers($user);
    $similar_user_similarity_mapping = [];

    foreach ($user_similarity_relations as $relation)
    {
      $id_of_similar_user = ($relation->getFirstUserId() !== $user->getId()) ?
        $relation->getFirstUserId() : $relation->getSecondUserId();
      $similar_user_similarity_mapping[$id_of_similar_user] = $relation->getSimilarity();
    }

    $ids_of_similar_users = array_keys($similar_user_similarity_mapping);
    $excluded_ids_of_liked_programs = array_unique(array_map(function (ProgramLike $like) {
      return $like->getProgramId();
    }, $all_likes_of_user));

    $differing_likes = $this->program_like_repository->getLikesOfUsers(
      $ids_of_similar_users, $user->getId(), $excluded_ids_of_liked_programs, $flavor);

    $recommendation_weights = [];
    $number_of_recommendations = [];
    $programs_liked_by_others = [];
    foreach ($differing_likes as $differing_like)
    {
      $key = $differing_like->getProgramId();
      assert(!in_array($key, $excluded_ids_of_liked_programs));

      if (!array_key_exists($key, $recommendation_weights))
      {
        $recommendation_weights[$key] = 0.0;
        $number_of_recommendations[$key] = 0;
        $programs_liked_by_others[$key] = $differing_like->getProgram();
      }

      $recommendation_weights[$key] += $similar_user_similarity_mapping[$differing_like->getUserId()];
      $number_of_recommendations[$key]++;
    }

    arsort($recommendation_weights);
    $recommendations_by_id = array_keys($recommendation_weights);

    /*
     * In order to present more diverse recommendations, they are now re-ranked. The
     * re-ranking algorithm is based on the paper
     * "Improving AggregateRecommendation Diversity Using Ranking-Based Techniques" by
     * Gediminas Adomavicius, Member, IEEE, and YoungOk Kwon.
     *
     *
     * Basically we want to recommend less popular items without sacrificing accuracy (=
     * the chance that the user downloads the recommendation). In order to achieve this we
     * have a look at the average weights of the recommendations and assume that, if the
     * average weight is above a certain threshold, it's a good recommendation, irrelevant
     * of it's total weight. Therefore we get a number of good recommendations that we can
     * re-rank after another criteria, which is popularity (= total number of likes).
     * That means all programs above the threshold are ranked from lowest to highest
     * popularity.
     *
     * There is a second threshold which is higher than the first one. The second one's
     * purpose is to increase the accuracy in the case that enough programs with high
     * average weights are found (because higher average weights mean higher accuracy).
     */
    $average_user_similarity = array_sum($similar_user_similarity_mapping) / count($similar_user_similarity_mapping);
    $threshold_above_average_weight = $average_user_similarity * 1.25;
    $threshold_high_weight = $average_user_similarity * 1.5;
    $average_recommendation_weight = [];
    $above_average_recommendation = [];
    $top_recommendation = [];

    foreach ($recommendations_by_id as $key => $recommendation_id)
    {
      $average_recommendation_weight[$recommendation_id] = $recommendation_weights[$recommendation_id] /
        $number_of_recommendations[$recommendation_id];

      switch ($average_recommendation_weight[$recommendation_id])
      {
        case $average_recommendation_weight[$recommendation_id] >= $threshold_high_weight:
          $top_recommendation[$recommendation_id] =
            $this->program_like_repository->totalLikeCount($recommendation_id);
          break;
        case $average_recommendation_weight[$recommendation_id] >= $threshold_above_average_weight:
          $above_average_recommendation[$recommendation_id] =
            $this->program_like_repository->totalLikeCount($recommendation_id);
          break;
        default:
          // do nothing
      }
    }

    /*
     * The reason why the top_recommendations don't always get put in front of the
     * above_average_recommendations is that if there is only a very small number of
     * top recommendations, the chance that those are only popular programs with high
     * total-weights is rather high. Since we want to make sure to recommend less popular
     * programs that is not desired.
     *
     * There is no minimum number for above_average_recommendations on the other hand
     * since after above_average_recommendations there is only the regular recommendations
     * left which usually recommend popular items anyway.
     */
    if (count($top_recommendation) >= 12)
    {
      asort($top_recommendation);
      asort($above_average_recommendation);
      $recommendations_by_id = array_merge(array_keys($above_average_recommendation), $recommendations_by_id);
      $recommendations_by_id = array_merge(array_keys($top_recommendation), $recommendations_by_id);
      $recommendations_by_id = array_unique($recommendations_by_id);
    }
    elseif (count($above_average_recommendation) > 0 || count($top_recommendation) > 0)
    {
      $above_average_recommendation = $above_average_recommendation + $top_recommendation;
      asort($above_average_recommendation);
      $recommendations_by_id = array_merge(array_keys($above_average_recommendation), $recommendations_by_id);
      $recommendations_by_id = array_unique($recommendations_by_id);
    }

    // $recommendation_by_id only holds the program ids. In order to return an array with
    // elements of the program entity $programs_liked_by_others is used.
    $programs = array_map(function ($program_id) use ($programs_liked_by_others) {
      return $programs_liked_by_others[$program_id];
    }, $recommendations_by_id);

    return ProgramRepository::filterVisiblePrograms(
      $programs, $this->app_request->isDebugBuildRequest()
    );
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
   * @throws DBALException
   */
  public function computeUserRemixSimilarities($progress_bar = null)
  {
    // TODO: consider backward & scratch relations too... (but very low priority as they won't affect the recommendations significantly!)
    $statement = $this->entity_manager->getConnection()
      ->prepare("SELECT MAX(id) as id_of_last_user FROM fos_user");
    $statement->execute();
    $id_of_last_user = intval($statement->fetch()['id_of_last_user']);
    $user_remix_relations = [];

    for ($user_id = 1; $user_id <= $id_of_last_user; $user_id++)
    {
      if ($progress_bar !== null)
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
        if ($progress_bar !== null)
        {
          $progress_bar->setMessage('(' . $user_counter . '/' . $total_number_of_remixed_users
            . ') - Computing remix similarity between user #' . $first_user_id . ' and user #' . $second_user_id);
        }

        $key = $first_user_id . '_' . $second_user_id;
        $reverse_key = $second_user_id . '_' . $first_user_id;

        if (($first_user_id === $second_user_id) || in_array($key, $already_added_relations)
          || in_array($reverse_key, $already_added_relations)
        )
        {
          continue;
        }

        $already_added_relations[] = $key;
        $ids_of_programs_remixed_by_second_user = array_unique(array_map(function ($data) {
          return $data['ancestor_id'];
        }, $second_user_remix_relations));

        $ids_of_same_programs_remixed_by_both = array_unique(
          array_intersect(
            $ids_of_programs_remixed_by_first_user, $ids_of_programs_remixed_by_second_user
          )
        );
        // make copy of array -> merge with empty array is fast shortcut!
        $temp = array_merge([], $ids_of_programs_remixed_by_first_user);
        // this imitate merge is way more faster than using array_merge() with huge arrays!
        // -> this has a significant impact on performance here!
        $this->imitateMerge($temp, $ids_of_programs_remixed_by_second_user);
        $ids_of_all_programs_remixed_by_any_of_both = array_unique($temp);

        $number_of_same_programs_remixed_by_both = count($ids_of_same_programs_remixed_by_both);
        $number_of_all_programs_remixed_by_any_of_both = count($ids_of_all_programs_remixed_by_any_of_both);

        if ($number_of_same_programs_remixed_by_both === 0)
        {
          continue;
        }

        $jaccard_similarity = floatval($number_of_same_programs_remixed_by_both) /
          floatval($number_of_all_programs_remixed_by_any_of_both);
        if ($jaccard_similarity >= 0.01)
        {
          $this->user_remix_similarity_relation_repository->insertRelation(
            $first_user_id, $second_user_id, $jaccard_similarity
          );
        }

        if ($progress_bar !== null)
        {
          $progress_bar->clear();
          $progress_bar->advance();
          $progress_bar->display();
        }
      }
    }
  }

  /**
   * @param User $user
   * @param      $flavor
   *
   * @return Program[]
   */
  public function recommendProgramsOfRemixSimilarUsers($user, $flavor)
  {
    // NOTE: this parameter should/can be increased after A/B testing has ended!
    //       -> meaningful values for this simple algorithm would be between 3-4
    // NOTE: If you modify this parameter, some tests will intentionally fail
    //       as they rely on the value of this parameter!
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

    foreach ($user_similarity_relations as $relation)
    {
      $id_of_similar_user = ($relation->getFirstUserId() !== $user->getId()) ?
        $relation->getFirstUserId() : $relation->getSecondUserId();
      $similar_user_similarity_mapping[$id_of_similar_user] = $relation->getSimilarity();
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

    $programs = array_map(function ($program_id) use ($programs_remixed_by_others) {
      return $programs_remixed_by_others[$program_id];
    }, array_keys($recommendation_weights));

    return ProgramRepository::filterVisiblePrograms(
      $programs, $this->app_request->isDebugBuildRequest()
    );
  }
}
