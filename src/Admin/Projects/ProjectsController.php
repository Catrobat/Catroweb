<?php

declare(strict_types=1);

namespace App\Admin\Projects;

use App\DB\Entity\Project\Project;
use App\Storage\StorageLifecycleService;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @phpstan-extends CRUDController<Project>
 */
class ProjectsController extends CRUDController
{
  public function __construct(
    private readonly StorageLifecycleService $storage_lifecycle,
  ) {
  }

  public function hardDeleteAction(): RedirectResponse
  {
    $project = $this->getProjectOrRedirect();
    if ($project instanceof RedirectResponse) {
      return $project;
    }

    $name = $project->getName();

    try {
      $this->storage_lifecycle->hardDeleteProject($project);
      $this->addFlash('sonata_flash_success', 'Project "'.$name.'" permanently deleted.');
    } catch (\Throwable $e) {
      $this->addFlash('sonata_flash_error', 'Failed to delete project.');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function softDeleteAction(): RedirectResponse
  {
    $project = $this->getProjectOrRedirect();
    if ($project instanceof RedirectResponse) {
      return $project;
    }

    $project->setVisible(false);
    $this->admin->getModelManager()->update($project);
    $this->addFlash('sonata_flash_success', 'Project "'.$project->getName().'" hidden.');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function toggleProtectedAction(): RedirectResponse
  {
    $project = $this->getProjectOrRedirect();
    if ($project instanceof RedirectResponse) {
      return $project;
    }

    $project->setStorageProtected(!$project->isStorageProtected());
    $this->admin->getModelManager()->update($project);

    $status = $project->isStorageProtected() ? 'protected' : 'unprotected';
    $this->addFlash('sonata_flash_success', 'Project "'.$project->getName().'" '.$status.'.');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function toggleApprovedAction(): RedirectResponse
  {
    $project = $this->getProjectOrRedirect();
    if ($project instanceof RedirectResponse) {
      return $project;
    }

    $project->setApproved(!$project->getApproved());
    $this->admin->getModelManager()->update($project);

    $status = $project->getApproved() ? 'whitelisted' : 'unwhitelisted';
    $this->addFlash('sonata_flash_success', 'Project "'.$project->getName().'" '.$status.'.');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  private function getProjectOrRedirect(): Project|RedirectResponse
  {
    /** @var Project|null $project */
    $project = $this->admin->getSubject();
    if (null === $project) {
      $this->addFlash('sonata_flash_error', 'Project not found.');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    return $project;
  }
}
