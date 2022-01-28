<?php

namespace App\Catrobat\Controller\Ci;

use App\Entity\Program;
use App\Manager\ProgramManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class ApkStatusController.
 *
 * @deprecated - Move to Catroweb-API
 */
class ApkStatusController extends AbstractController
{
  private ProgramManager $program_manager;

  private TranslatorInterface $translator;

  public function __construct(ProgramManager $program_manager, TranslatorInterface $translator)
  {
    $this->program_manager = $program_manager;
    $this->translator = $translator;
  }

  /**
   * @Route("/ci/status/{id}", name="ci_status", defaults={"_format": "json"}, methods={"GET"})
   */
  public function getApkStatusAction(string $id): JsonResponse
  {
    /** @var Program|null $program */
    $program = $this->program_manager->find($id);

    if (null === $program || !$program->isVisible()) {
      throw new NotFoundHttpException();
    }

    $result = [];
    switch ($program->getApkStatus()) {
      case Program::APK_READY:
        $result['status'] = 'ready';
        $result['url'] = $this->generateUrl('ci_download',
          ['id' => $program->getId(), 'fname' => $program->getName()], UrlGeneratorInterface::ABSOLUTE_URL);
        $result['label'] = $this->translator->trans('ci.download', [], 'catroweb');
        break;
      case Program::APK_PENDING:
        $result['status'] = 'pending';
        $result['label'] = $this->translator->trans('ci.pending', [], 'catroweb');
        break;
      default:
        $result['label'] = $this->translator->trans('ci.generate', [], 'catroweb');
        $result['status'] = 'none';
    }

    return JsonResponse::create($result);
  }
}
