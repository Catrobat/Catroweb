<?php
namespace Catrobat\AppBundle\Controller\Ci;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class DownloadApkController extends Controller
{
    /**
     * @Route("/ci/download/{id}", name="ci_download", requirements={"id": "\d+"})
     * @Method({"GET"})
     */
    public function downloadApkAction(Request $request, Program $program)
    {
        if (!$program->isVisible())
        {
            throw new NotFoundHttpException();
        }
        if ($program->getApkStatus() != Program::APK_READY)
        {
            throw new NotFoundHttpException();
        }
        
        /* @var $apkrepository \Catrobat\AppBundle\Services\ApkRepository */
        $apkrepository = $this->get("apkrepository");
        
        try
        {
          $file = $apkrepository->getProgramFile($program->getId());
        }
        catch (\Exception $e)
        {
            throw new NotFoundHttpException();
        }
        if ($file->isFile())
        {
            return new BinaryFileResponse($file);
        }
        throw new NotFoundHttpException();
    }
}