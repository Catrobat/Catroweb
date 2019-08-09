<?php

namespace App\Catrobat\Controller\Ci;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Program;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;


/**
 * Class ApkStatusController
 * @package App\Catrobat\Controller\Ci
 */
class ApkStatusController extends Controller
{

  /**
   * @Route("/ci/status/{id}", name="ci_status", defaults={"_format": "json"}, methods={"GET"})
   *
   * @param Program $program
   *
   * @return JsonResponse
   */
  public function getApkStatusAction(Program $program)
  {
    $result = [];
    switch ($program->getApkStatus())
    {
      case Program::APK_READY:
        $result['status'] = 'ready';
        $result['url'] = $this->generateUrl('ci_download',
          ['id' => $program->getId(), 'fname' => $program->getName()], UrlGeneratorInterface::ABSOLUTE_URL);
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
