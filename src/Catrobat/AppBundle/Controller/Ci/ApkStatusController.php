<?php

namespace Catrobat\AppBundle\Controller\Ci;

use Catrobat\AppBundle\Controller\Web\DefaultController;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class ApkStatusController extends Controller
{
  /**
   * @Route("/ci/status/{id}", name="ci_status", defaults={"_format": "json"}, requirements={"id": "\d+"})
   * @Method({"GET"})
   */
  public function getApkStatusAction(Request $request, Program $program)
  {
    $result = [];
    switch ($program->getApkStatus())
    {
      case Program::APK_READY:
        $result['status'] = 'ready';
        $result['url'] = $this->generateUrl('ci_download', ['id' => $program->getId(), 'fname' => $program->getName()], UrlGeneratorInterface::ABSOLUTE_URL);
        $result['label'] = $this->get('translator')->trans('ci.download', [], 'catroweb');
        break;
      case Program::APK_PENDING:
        $result['status'] = 'pending';
        $result['label'] = $this->get('translator')->trans('ci.pending', [], 'catroweb');
        break;
      default:
        $result['label'] = $this->get('translator')->trans('ci.generate', [], 'catroweb');
        $result['status'] = 'none';
    }

    return JsonResponse::create($result);
  }
}
