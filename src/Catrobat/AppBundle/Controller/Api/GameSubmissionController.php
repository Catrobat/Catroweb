<?php
namespace Catrobat\AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\Entity\GameJam;
use Catrobat\AppBundle\Exceptions\InvalidCatrobatFileException;
use Catrobat\AppBundle\Exceptions\Upload\NoGameJamException;
use Catrobat\AppBundle\Responses\ProgramListResponse;

class GameSubmissionController extends Controller
{

    /**
     * @Route("/api/gamejam/submit/{id}", name="gamejam_form_submission")
     * @Method({"GET"})
     */
    public function formSubmittedAction(Request $request, Program $program)
    {
        if ($program->getGamejam() != null) {
            $program->setAccepted(true);
            $this->getDoctrine()
                ->getManager()
                ->persist($program);
            $this->getDoctrine()
                ->getManager()
                ->flush();
            return JsonResponse::create(array(
                "statusCode" => "200",
                "message" => "Program accepted for this gamejam"
            ));
        }
        else
        {
            return JsonResponse::create(array(
                "statusCode" => "999",
                "message" => "This program was not submitted to a gamejam"
            ));
        }
        
    }
    
    /**
     * @Route("/api/gamejam/sampleprograms.json", name="api_gamejam_sample_programs")
     * @Method({"GET"})
     */
    public function getSampleProgramsForCurrentGamejam()
    {
        $gamejam = $this->get("gamejamrepository")->getCurrentGameJam();
        if ($gamejam == null)
        {
            throw new NoGameJamException();
        }
        return new ProgramListResponse($gamejam->getSamplePrograms(), count($gamejam->getSamplePrograms()));
    }
    
}