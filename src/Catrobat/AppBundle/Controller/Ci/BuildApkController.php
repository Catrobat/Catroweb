<?php
namespace Catrobat\AppBundle\Controller\Ci;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;


class BuildApkController extends Controller
{   
    /**
    * @Route("/ci/build/{id}", name="ci_build", defaults={"_format": "json"}, requirements={"id": "\d+"})
    * @Method({"GET"})
    */
    public function createApkAction(Request $request, Program $program) 
    {
        if (!$program->isVisible())
        {
            throw $this->createNotFoundException();
        }
        $config = $this->container->getParameter("jenkins");
        
        $dispatcher = $this->get("ci.jenkins.dispatcher");
        $call = $dispatcher->sendBuildRequest($program->getId());
        
        $config['call'] = $call;
        return JsonResponse::create($config);
    }
    
    /**
     * @Route("/ci/upload/{id}", name="ci_upload_apk", defaults={"_format": "json"}, requirements={"id": "\d+"})
     * @Method({"GET", "POST"})
     */
    public function uploadApkAction(Request $request, Program $program)
    {
        $config = $this->container->getParameter("jenkins");
        if ($request->query->get('uploadtoken') !== $config['uploadtoken'])
        {
            return JsonResponse::create(array("error" => "invalid token"));
        }
        else if ($request->files->count() != 1)
        {
           return JsonResponse::create(array("error" => "no file given"));
        }
        else
        {
            $file = array_values($request->files->all())[0];
        }
        return JsonResponse::create($config);
    }
    
}