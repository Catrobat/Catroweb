<?php

namespace Catrobat\AppBundle\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Validator\Constraints\Null;
use Symfony\Component\Validator\Validator;
use Symfony\Component\Security\Core\SecurityContext;
use Catrobat\AppBundle\Model\UserManager;
use Catrobat\AppBundle\Model\ProgramManager;
use Catrobat\AppBundle\Services\TokenGenerator;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Catrobat\AppBundle\StatusCode;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Catrobat\AppBundle\Entity\ProgramInappropriateReport;

class ReportController extends Controller
{
  /**
   * @Route("/api/reportProgram/reportProgram.json", name="catrobat_api_report_program", defaults={"_format": "json"})
   * @Method({"POST","GET"})
   */
  public function reportProgramAction(Request $request)
  {
    /* @var $context \Symfony\Component\Security\Core\SecurityContext */
    /* @var $programmanager \Catrobat\AppBundle\Model\ProgramManager */

    $context = $this->get("security.context");
    $programmanager = $this->get("programmanager");
    $entityManager = $this->getDoctrine()->getManager();

    $response = array();
    if(!$request->get('program') || !$request->get('note'))
    {
      $response["printr"] = print_r($request,true);
      $response["statusCode"] = StatusCode::MISSING_POST_DATA;
      $response["answer"] = $this->trans("error.post-data");
      $response["preHeaderMessages"] = "";
      return JsonResponse::create($response);
    }

    $program = $programmanager->find($request->get('program'));
    if($program == null)
    {
      $response["statusCode"] = StatusCode::INVALID_PROGRAM;
      $response["answer"] = $this->trans("error.program.invalid");
      $response["preHeaderMessages"] = "";
      return JsonResponse::create($response);
    }

    $report = new ProgramInappropriateReport();

    if($context->isGranted("IS_AUTHENTICATED_REMEMBERED"))
    {
      $report->setReportingUser($context->getToken()->getUser()); //could be anon
    }else
      $report->setReportingUser(NULL); //could be anon

    $report->setNote($request->get('note'));
    $report->setProject($program);

    $entityManager->persist($report);
    //Do we need an event dispatcher?

    echo "done";
    $entityManager->flush();

    $response = array();
    $response["answer"] = $this->trans("success.report");
    $response["statusCode"] = StatusCode::OK;

    return JsonResponse::create($response);
  }

  private function trans($message, $parameters = array())
  {
    return  $this->get("translator")->trans($message,$parameters,"catroweb");
  }
}
