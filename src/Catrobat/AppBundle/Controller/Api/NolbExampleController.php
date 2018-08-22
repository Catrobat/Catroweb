<?php
namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Entity\NolbExampleProgram;
use Catrobat\AppBundle\Responses\ProgramListResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Entity\NolbExampleRepository;

class NolbExampleController extends Controller
{

    /**
     * @Route("/api/nolb/example.json", name="api_nolb_examples", methods={"GET"})
     */
    public function getNolbExampleProgramsAction(Request $request) {
        /* @var $repository NolbExampleRepository
         * @var $example NolbExampleProgram
         */

        $repository = $this->get('nolbexamplerepository');

        $limit = intval($request->query->get('limit'));
        $offset = intval($request->query->get('offset'));

        $examples = $repository->getActivePrograms($limit, $offset);
        $numbOfTotalProjects = $repository->getActiveProgramsCount();

        $male = array();
        $female = array();
        $programs = array();
        $count = count($examples);

        foreach ($examples as $example) {
          if ($example->getIsForFemale()) {
            array_push($female, $example->getProgram());
          }
          else {
            array_push($male, $example->getProgram());
          }
        }

        $male_count = count($male);
        $female_count = count($female);

        $flip = $male_count >= $female_count;
        for($i = 0; $i < $count; $i++) {
          if($male_count == 0) {
            $programs = array_merge($programs, $female);
            break;
          }

          if($female_count == 0) {
            $programs = array_merge($programs, $male);
            break;
          }

          if($flip) {
            array_push($programs, array_pop($male));
            $male_count--;
          }
          else {
            array_push($programs, array_pop($female));
            $female_count--;
          }

          $flip = !$flip;
        }

        return new ProgramListResponse($programs, $numbOfTotalProjects, true);
    }
}
