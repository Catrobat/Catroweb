<?php
namespace Catrobat\AppBundle\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;

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
}