<?php

namespace Catrobat\AppBundle\Controller\Ci;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApkStatusController extends Controller
{
    /**
     * @Route("/ci/status/{id}", name="ci_status", defaults={"_format": "json"}, requirements={"id": "\d+"})
     * @Method({"GET"})
     */
    public function getApkStatusAction(Request $request, Program $program) {
        $result = array();
        switch ($program->getApkStatus()) {
            case Program::APK_READY:
                $result['status'] = 'ready';
                $result['url'] = $this->generateUrl('ci_download', array('id' => $program->getId(), 'fname' => $program->getName()), true);
                $result['label'] = $this->get('translator')->trans('ci.download', array(), 'catroweb');
                break;
            case Program::APK_PENDING:
                $result['status'] = 'pending';
                $result['label'] = $this->get('translator')->trans('ci.pending', array(), 'catroweb');
                break;
            default:
                $result['label'] = $this->get('translator')->trans('ci.generate', array(), 'catroweb');
                $result['status'] = 'none';
        }
        return JsonResponse::create($result);
    }
}
