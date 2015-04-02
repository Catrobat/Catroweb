<?php
namespace Catrobat\AppBundle\Controller\Ci;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;
use Catrobat\AppBundle\Model\ProgramManager;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


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
        if ($program->getApkStatus() !== Program::APK_NONE)
        {
            return JsonResponse::create(array("error" => "already built"));
        }
        
        $config = $this->container->getParameter("jenkins");
        
        $dispatcher = $this->get("ci.jenkins.dispatcher");
        $call = $dispatcher->sendBuildRequest($program->getId());
        
        $program->setApkStatus(Program::APK_PENDING);
        $this->get("programmanager")->save($program);
        
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
        if ($request->query->get('token') !== $config['uploadtoken'])
        {
            throw new AccessDeniedException();
        }
        else if ($request->files->count() != 1)
        {
           throw new BadRequestHttpException();
        }
        else
        {
            $file = array_values($request->files->all())[0];
            /* @var $apkrepository \Catrobat\AppBundle\Services\ApkRepository */
            $apkrepository = $this->get('apkrepository');
            $apkrepository->save($file, $program->getId());
            $program->setApkStatus(Program::APK_READY);
            $this->get('programmanager')->save($program);
        }
        return JsonResponse::create($config);
    }
    
    /**
     * @Route("/ci/failed/{id}", name="ci_failed_apk", defaults={"_format": "json"}, requirements={"id": "\d+"})
     * @Method({"GET"})
     */
    public function failedApkAction(Request $request, Program $program)
    {
        $config = $this->container->getParameter("jenkins");
        if ($request->query->get('token') !== $config['uploadtoken'])
        {
            throw new AccessDeniedException();
        }
        if ($program->getApkStatus() === Program::APK_PENDING)
        {
            $program->setApkStatus(Program::APK_NONE);
            $this->get('programmanager')->save($program);
            return JsonResponse::create(array("OK"));
        }
        return JsonResponse::create(array("error" => "program is not building"));
    }
}