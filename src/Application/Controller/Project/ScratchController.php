<?php

declare(strict_types=1);

namespace App\Application\Controller\Project;

use App\Project\Scratch\Converter\ScratchConversionException;
use App\Project\Scratch\ScratchManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\RateLimiter\RateLimiterFactoryInterface;
use Symfony\Component\Routing\Attribute\Route;

class ScratchController extends AbstractController
{
  public function __construct(
    private readonly ScratchManager $scratch_manager,
    private readonly RateLimiterFactoryInterface $scratchOpenBurstLimiter,
    private readonly RateLimiterFactoryInterface $scratchConvertBurstLimiter,
    private readonly RateLimiterFactoryInterface $scratchConvertDailyLimiter,
  ) {
  }

  /** Opens a Scratch project in Pocket Code form, converting it on first use or when it changed on Scratch. */
  #[Route(path: '/scratch/project/{id}', name: 'scratch_project', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
  public function scratchProject(Request $request, int $id): Response
  {
    $ip = $request->getClientIp() ?? 'unknown';
    try {
      if (!$this->scratchOpenBurstLimiter->create($ip)->consume()->isAccepted()) {
        throw new ScratchConversionException('Too many Scratch projects opened in a short time. Please wait a minute.');
      }

      $project = $this->scratch_manager->importProject($id, function () use ($ip): void {
        $burst = $this->scratchConvertBurstLimiter->create($ip)->consume();
        $daily = $this->scratchConvertDailyLimiter->create($ip)->consume();
        if (!$burst->isAccepted() || !$daily->isAccepted()) {
          throw new ScratchConversionException('You converted many Scratch projects in a short time. Please try again later.');
        }
      }, $ip);
    } catch (ScratchConversionException $exception) {
      if ($request->isMethod('POST')) {
        return new Response($exception->getMessage(), Response::HTTP_UNPROCESSABLE_ENTITY);
      }

      $this->addFlash('snackbar', $exception->getMessage());

      return $this->redirectToRoute('index');
    }

    $url = $this->generateUrl('project', ['id' => $project->getId()]);
    if ($request->isMethod('GET')) {
      return $this->redirect($url);
    }

    return new Response((string) $project->getId(), Response::HTTP_CREATED, ['Location' => $url]);
  }
}
