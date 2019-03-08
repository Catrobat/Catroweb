<?php

namespace App\Catrobat\Controller\Api;

use App\Entity\NolbExampleProgram;
use App\Catrobat\Responses\ProgramListResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use App\Repository\NolbExampleRepository;


/**
 * Class NolbExampleController
 * @package App\Catrobat\Controller\Api
 */
class NolbExampleController extends Controller
{

  /**
   *
   * @Route("/api/nolb/example.json", name="api_nolb_examples", methods={"GET"})
   *
   * @param Request $request
   *
   * @return ProgramListResponse
   */
  public function getNolbExampleProgramsAction(Request $request)
  {
    /* @var $repository NolbExampleRepository
     * @var $example NolbExampleProgram
     */

    $repository = $this->get('nolbexamplerepository');

    $limit = intval($request->query->get('limit'));
    $offset = intval($request->query->get('offset'));

    $examples = $repository->getActivePrograms($limit, $offset);
    $numbOfTotalProjects = $repository->getActiveProgramsCount();

    $male = [];
    $female = [];
    $programs = [];
    $count = count($examples);

    foreach ($examples as $example)
    {
      if ($example->getIsForFemale())
      {
        array_push($female, $example->getProgram());
      }
      else
      {
        array_push($male, $example->getProgram());
      }
    }

    $male_count = count($male);
    $female_count = count($female);

    $flip = $male_count >= $female_count;
    for ($i = 0; $i < $count; $i++)
    {
      if ($male_count == 0)
      {
        $programs = array_merge($programs, $female);
        break;
      }

      if ($female_count == 0)
      {
        $programs = array_merge($programs, $male);
        break;
      }

      if ($flip)
      {
        array_push($programs, array_pop($male));
        $male_count--;
      }
      else
      {
        array_push($programs, array_pop($female));
        $female_count--;
      }

      $flip = !$flip;
    }

    return new ProgramListResponse($programs, $numbOfTotalProjects, true);
  }
}
