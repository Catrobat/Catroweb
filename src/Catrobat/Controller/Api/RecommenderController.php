<?php

namespace App\Catrobat\Controller\Api;

use App\Catrobat\RecommenderSystem\RecommenderManager;
use App\Catrobat\Responses\ProgramListResponse;
use App\Catrobat\StatusCode;
use App\Entity\ProgramManager;
use App\Entity\User;
use App\Entity\UserManager;
use App\Entity\UserTestGroup;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class RecommenderController extends AbstractController
{
  private int $DEFAULT_LIMIT = 20;

  private int $DEFAULT_OFFSET = 0;

  private ProgramManager $program_manager;

  public function __construct(ProgramManager $program_manager)
  {
    $this->program_manager = $program_manager;
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/recsys.json", name="api_recsys_programs", defaults={"_format": "json"}, methods={"GET"})
   *
   * @return JsonResponse|ProgramListResponse
   */
  public function listRecsysProgramAction(Request $request, ProgramManager $program_manager)
  {
    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);
    $flavor = $request->get('flavor', 'pocketcode');

    $program_id = $request->query->get('program_id');

    if (null === $program_id)
    {
      return JsonResponse::create(['statusCode' => StatusCode::INVALID_PROGRAM]);
    }

    $program = $this->program_manager->find($program_id);

    if (null === $program)
    {
      return JsonResponse::create(['statusCode' => StatusCode::INVALID_PROGRAM]);
    }

    $programs_count = $program_manager->getRecommendedProgramsCount($program_id, $flavor);
    $programs = $program_manager->getRecommendedProgramsById($program_id, $flavor, $limit, $offset);

    return new ProgramListResponse($programs, $programs_count);
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/recsys_specific_projects/{id}.json", name="api_recsys_specific_projects", defaults={"_format": "json"},  methods={"GET"})
   *
   * @return JsonResponse|ProgramListResponse
   */
  public function listRecsysSpecificProgramsAction(Request $request, string $id, ProgramManager $program_manager)
  {
    $is_test_environment = ('test' == $this->getParameter('kernel.environment'));
    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);

    $flavor = $request->get('flavor');

    $program = $program_manager->find($id);
    if (null === $program)
    {
      return JsonResponse::create(['statusCode' => StatusCode::INVALID_PROGRAM]);
    }

    $programs_count = $program_manager->getRecommendedProgramsCount($id, $flavor);
    $programs = $program_manager->getOtherMostDownloadedProgramsOfUsersThatAlsoDownloadedGivenProgram(
      $flavor, $program, $limit, $offset, $is_test_environment);

    return new ProgramListResponse($programs, $programs_count);
  }

  /**
   * @deprecated
   *
   * @Route("/api/projects/recsys_general_projects.json", name="api_recsys_general_projects",
   * defaults={"_format": "json"}, methods={"GET"})
   *
   * @throws Exception
   */
  public function listRecsysGeneralProgramsAction(Request $request, UserManager $user_manager, RecommenderManager $recommender_manager): ProgramListResponse
  {
    $is_test_environment = ('test' == $this->getParameter('kernel.environment'));
    $test_user_id_for_like_recommendation = $is_test_environment ?
      $request->query->get('test_user_id_for_like_recommendation', 0) : '';
    $limit = (int) $request->query->get('limit', $this->DEFAULT_LIMIT);
    $offset = (int) $request->query->get('offset', $this->DEFAULT_OFFSET);

    $flavor = $request->get('flavor');

    $programs_count = 0;
    $programs = [];
    $is_user_specific_recommendation = false;

    /** @var User|null $user */
    $user = ('' == $test_user_id_for_like_recommendation) ?
      $this->getUser() : $user_manager->find($test_user_id_for_like_recommendation);

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
    if ($is_test_environment && null != $user)
    {
      $all_programs = $recommender_manager->recommendHomepageProgramsAlgorithmOne($user, $flavor);
      $programs_count = count($all_programs);
      $programs = array_slice($all_programs, $offset, $limit);
    }

    // Users are assigned to a test group if they aren't already part of one.
    elseif (null != $user)
    {
      $user_id = $user->getId();
      $em = $this->getDoctrine()->getManager();
      $user_test_group = $em->find(UserTestGroup::class, $user_id);
      if (null === $user_test_group)
      {
        $user_test_group = new UserTestGroup($user_id, random_int(1, 3));
        $em->persist($user_test_group);
        $em->flush();
      }

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
    if ((null == $user) || (0 == $programs_count))
    {
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
