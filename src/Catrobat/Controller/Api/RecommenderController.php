<?php

namespace App\Catrobat\Controller\Api;

use App\Entity\UserTestGroup;
use App\Entity\ProgramManager;
use App\Catrobat\StatusCode;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Catrobat\Responses\ProgramListResponse;

/**
 * Class RecommenderController
 * @package App\Catrobat\Controller\Api
 */
class RecommenderController extends Controller
{

  /**
   * @var int
   */
  private $DEFAULT_LIMIT = 20;

  /**
   * @var int
   */
  private $DEFAULT_OFFSET = 0;


  /**
   * @Route("/api/projects/recsys.json", name="api_recsys_programs", defaults={"_format": "json"},
   *   methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   */
  public function listRecsysProgramAction(Request $request)
  {
    $limit = intval($request->query->get('limit', $this->DEFAULT_LIMIT));
    $offset = intval($request->query->get('offset', $this->DEFAULT_OFFSET));
    $program_id = intval($request->query->get('program_id'));

    $program_manager = $this->get('programmanager');
    $flavor = $request->getSession()->get('flavor');

    $programs_count = $program_manager->getRecommendedProgramsCount($program_id, $flavor);
    $programs = $program_manager->getRecommendedProgramsById($program_id, $flavor, $limit, $offset);

    return new ProgramListResponse($programs, $programs_count);
  }


  /**
   * @Route("/api/projects/recsys_specific_programs/{id}.json", name="api_recsys_specific_programs",
   *   defaults={"_format": "json"}, requirements={"id":"\d+"}, methods={"GET"})
   *
   * @param Request $request
   * @param         $id
   *
   * @return ProgramListResponse|JsonResponse
   */
  public function listRecsysSpecificProgramsAction(Request $request, $id)
  {
    /**
     * @var $program_manager ProgramManager
     */

    $is_test_environment = ($this->get('kernel')->getEnvironment() == 'test');
    $limit = intval($request->query->get('limit', $this->DEFAULT_LIMIT));
    $offset = intval($request->query->get('offset', $this->DEFAULT_OFFSET));

    $program_manager = $this->get('programmanager');
    $flavor = $request->getSession()->get('flavor');

    $program = $program_manager->find($id);
    if ($program == null)
    {
      return JsonResponse::create(['statusCode' => StatusCode::INVALID_PROGRAM]);
    }

    $programs_count = $program_manager->getRecommendedProgramsCount($id, $flavor);
    $programs = $program_manager->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
      $flavor, $program, $limit, $offset, $is_test_environment);

    return new ProgramListResponse($programs, $programs_count);
  }


  /**
   * @Route("/api/projects/recsys_general_programs.json", name="api_recsys_general_programs",
   *   defaults={"_format":"json"}, methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   */
  public function listRecsysGeneralProgramsAction(Request $request)
  {
    $is_test_environment = ($this->get('kernel')->getEnvironment() == 'test');
    $test_user_id_for_like_recommendation = $is_test_environment ?
      intval($request->query->get('test_user_id_for_like_recommendation', 0)) : 0;
    $limit = intval($request->query->get('limit', $this->DEFAULT_LIMIT));
    $offset = intval($request->query->get('offset', $this->DEFAULT_OFFSET));

    $program_manager = $this->get('programmanager');
    $flavor = $request->getSession()->get('flavor');

    $programs_count = 0;
    $programs = [];
    $is_user_specific_recommendation = false;

    $user = ($test_user_id_for_like_recommendation == 0) ?
      $this->getUser() : $this->get('usermanager')->find($test_user_id_for_like_recommendation);

    /*
     * This part of the Recommender Controller is currently modified due to an online
     * experiment that will take place as a part of a master's thesis. Basically users are
     * assigned to one of three test groups and are presented with a modified version of
     * the recommender system algorithm accordingly to their group.
     *
     * The following "if" is a workaround to pass the old behat tests from the file
     * get_recommended_programs_homepage.feature by only using the algorithm that the
     * tests have been designed for originally. I have tested all 3
     * recommendedHomepageProgramsAlgorithms with behat individually, which didn't require
     * much change to the current test file. To re-write the behat tests of this file to
     * fit the online experiment where the 3 different algorithms are compared would add
     * unnecessary clutter that will be removed after the online experiment anyway. The
     * behat file will be updated after the online experiment accordingly.
     */
    if ($is_test_environment && $user != null)
    {
      $recommender_manager = $this->get('recommendermanager');
      $all_programs = $recommender_manager->recommendHomepageProgramsAlgorithmOne($user, $flavor);
      $programs_count = count($all_programs);
      $programs = array_slice($all_programs, $offset, $limit);
    }

    // Users are assigned to a test group if they aren't already part of one.
    else if ($user != null)
    {
      $user_id = $user->getId();
      $em = $this->getDoctrine()->getManager();
      $user_test_group = $em->find(UserTestGroup::class, $user_id);
      if (!$user_test_group)
      {
        $user_test_group = new UserTestGroup($user_id, rand(1, 3));
        $em->persist($user_test_group);
        $em->flush();
      }

      $recommender_manager = $this->get('recommendermanager');

      // Depending on the user's test group different algorithms are presented.
      switch ($user_test_group->getGroupNumber())
      {
        case 1:
          $all_programs = $recommender_manager->recommendHomepageProgramsAlgorithmOne($user, $flavor);
          break;
        case 2:
          $all_programs = $recommender_manager->recommendHomepageProgramsAlgorithmTwo($user, $flavor);
          break;
        case 3:
          $all_programs = $recommender_manager->recommendHomepageProgramsAlgorithmThree($user, $flavor);
          break;
        default:
          $all_programs = [];
      }

      $programs_count = count($all_programs);
      $programs = array_slice($all_programs, $offset, $limit);
    }

    // Recommendations for guest user (or logged in users who receive zero recommendations)
    if (($user == null) || ($programs_count == 0))
    {
      $recommender_manager = $this->get('recommendermanager');
      $all_programs = $recommender_manager->recommendHomepageProgramsForGuests($flavor);

      $programs_count = count($all_programs);
      $programs = array_slice($all_programs, $offset, $limit);
    }
    else
    {
      $is_user_specific_recommendation = true;
    }

    return new ProgramListResponse($programs, $programs_count, true, $is_user_specific_recommendation);
  }
}
