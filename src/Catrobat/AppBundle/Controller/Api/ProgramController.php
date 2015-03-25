<?php

namespace Catrobat\AppBundle\Controller\Api;

use Catrobat\AppBundle\Services\Formatter\ElapsedTimeStringFormatter;
use Catrobat\AppBundle\Model\ProgramManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\Services\ScreenshotRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;

class ProgramController extends Controller
{

  /**
   * @Route("/{flavor}/api/projects/getInfoById.json", name="api_info_by_id", defaults={"_format": "json"}, requirements={"flavor": "pocketcode|pocketkodey"})
   * @Route("/api/projects/getInfoById.json", name="catrobat_api_info_by_id", defaults={"_format": "json"})
   * @Method({"GET"})
   */
  public function showProgramAction(Request $request)
  {
    $id = intval($request->query->get('id'));
    $program_manager = $this->get("programmanager");
    $screenshot_repository = $this->get("screenshotrepository");
    $elapsed_time = $this->get("elapsedtime");
    
    $retArray = array ();
    
    $programs = array();
    $program = $program_manager->find($id);
    if ($program == null)
    {
        return JsonResponse::create(array("Error" => "Project not found (uploaded)", "preHeaderMessages" => ""));
    }
    else
    {
        $numbOfTotalProjects = 1;
        $programs[] = $program;
    }
    
    $retArray['CatrobatProjects'] = array ();
    foreach($programs as $program)
    {
      $new_program = array ();
      $new_program['ProjectId'] = $program->getId();
      $new_program['ProjectName'] = $program->getName();
      $new_program['ProjectNameShort'] = $program->getName();
      $new_program['Author'] = $program->getUser()->getUserName();
      $new_program['Description'] = $program->getDescription();
      $new_program['Version'] = $program->getCatrobatVersionName();
      $new_program['Views'] = $program->getViews();
      $new_program['Downloads'] = $program->getDownloads();
      $new_program['Uploaded'] = $program->getUploadedAt()->getTimestamp();
      $new_program['UploadedString'] = $elapsed_time->getElapsedTime($program->getUploadedAt()->getTimestamp());
      $new_program['ScreenshotBig'] = $screenshot_repository->getScreenshotWebPath($program->getId());
      $new_program['ScreenshotSmall'] = $screenshot_repository->getThumbnailWebPath($program->getId());
      $new_program['ProjectUrl'] = ltrim($this->generateUrl('program', array('flavor' => $request->attributes->get("flavor"), 'id' => $program->getId())),"/");
      $new_program['DownloadUrl'] = ltrim($this->generateUrl('download', array('id' => $program->getId())),"/");
      $new_program['FileSize'] = $program->getFilesize()/1048576;
      $retArray['CatrobatProjects'][] = $new_program;
    }
    $retArray['completeTerm'] = "";
    $retArray['preHeaderMessages'] = "";
    
    $retArray['CatrobatInformation'] = array (
        "BaseUrl" => ($request->isSecure() ? 'https://' : 'http://'). $request->getHttpHost() . $request->getBaseUrl() . '/',
        "TotalProjects" => $numbOfTotalProjects,
        "ProjectsExtension" => ".catrobat" 
    );
    
    return JsonResponse::create($retArray);
  }

}
