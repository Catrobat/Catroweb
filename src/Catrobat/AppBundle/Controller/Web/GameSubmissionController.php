<?php
namespace Catrobat\AppBundle\Controller\Web;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;

class GameSubmissionController extends Controller
{
    /**
     * @Route("/gamejam/submit/{id}", name="gamejam_form_submission")
     * @Method({"GET"})
     */
    public function formSubmittedAction(Request $request, Program $program)
    {
        $program->setAccepted(true);
        $this->getDoctrine()->getManager()->persist($program);
        $this->getDoctrine()->getManager()->flush();
        return JsonResponse::create(array("status" => "ok"));
    }
}