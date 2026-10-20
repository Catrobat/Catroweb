<?php

declare(strict_types=1);

namespace App\Project\Controller;

use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\ProjectManager;
use App\Project\ProtectedProjectFiles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;

#[AsController]
final class ProjectFilesController
{
  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly ExtractedFileRepository $extracted_files,
    private readonly ProtectedProjectFiles $files,
  ) {
  }

  public function asset(Request $request, string $id, string $path): Response
  {
    $raw_path = explode('?', $request->getRequestUri(), 2)[0];
    if (preg_match('/%(?:2f|5c|00|25)/i', $raw_path)) {
      return $this->deny();
    }
    $project = $this->project_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      return $this->deny();
    }
    // Extracted previews use the project-page policy, including its owner/admin exceptions.
    if (!is_dir($this->extracted_files->getBaseDir($id))) {
      $this->extracted_files->loadProjectExtractedFile($project);
    }

    return $this->files->asset($id, $path) ?? $this->deny();
  }

  public function deny(): Response
  {
    return new Response('', Response::HTTP_NOT_FOUND, ['Cache-Control' => 'private, no-store']);
  }
}
