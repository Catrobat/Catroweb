<?php

declare(strict_types=1);

namespace App\Admin\Projects\UploadProject;

use App\DB\Entity\Flavor;
use App\DB\Entity\User\User;
use App\Project\AddProjectRequest;
use App\Project\ProjectManager;
use App\User\UserManager;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @phpstan-extends CRUDController<\stdClass>
 */
class UploadProjectController extends CRUDController
{
  private const int MAX_FILE_SIZE_BYTES = 100 * 1024 * 1024;

  public function __construct(
    private readonly ProjectManager $project_manager,
    private readonly UserManager $user_manager,
    private readonly LoggerInterface $logger,
  ) {
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    if (!$this->admin->isGranted('LIST')) {
      throw new AccessDeniedException();
    }

    return $this->render('Admin/Projects/UploadProject/upload.html.twig', [
      'flavors' => Flavor::ALL,
    ]);
  }

  public function uploadAction(Request $request): Response
  {
    if (!$this->admin->isGranted('LIST')) {
      throw new AccessDeniedException();
    }

    if (!$request->isMethod('POST')) {
      return $this->redirectToRoute('admin_upload_project_list');
    }

    $contentLength = $request->server->get('CONTENT_LENGTH');
    if (null !== $contentLength && 0 === $request->request->count() && 0 === $request->files->count()) {
      $maxSize = ini_get('post_max_size') ?: '8M';
      $this->addFlash('sonata_flash_error', sprintf('The uploaded file is too large. PHP post_max_size is %s — increase it in php.ini or upload a smaller file.', $maxSize));

      return $this->redirectToRoute('admin_upload_project_list');
    }

    $username = trim((string) $request->request->get('username'));
    if ('' === $username) {
      $this->addFlash('sonata_flash_error', 'Please enter a username.');

      return $this->redirectToRoute('admin_upload_project_list');
    }

    $user = $this->user_manager->findUserByUsername($username);
    if (!$user instanceof User) {
      $this->addFlash('sonata_flash_error', sprintf('User "%s" not found.', $username));

      return $this->redirectToRoute('admin_upload_project_list');
    }

    /** @var UploadedFile|null $file */
    $file = $request->files->get('project_file');
    if (!$file instanceof UploadedFile) {
      $this->addFlash('sonata_flash_error', 'Please select a .catrobat file to upload.');

      return $this->redirectToRoute('admin_upload_project_list');
    }

    if (!$file->isValid()) {
      $this->addFlash('sonata_flash_error', sprintf('File upload error: %s', $file->getErrorMessage()));

      return $this->redirectToRoute('admin_upload_project_list');
    }

    if ($file->getSize() > self::MAX_FILE_SIZE_BYTES) {
      $this->addFlash('sonata_flash_error', sprintf('File size exceeds the 100 MB limit (uploaded: %s MB).', round($file->getSize() / (1024 * 1024), 2)));

      return $this->redirectToRoute('admin_upload_project_list');
    }

    $extension = $file->getClientOriginalExtension();
    if (!in_array(strtolower($extension), ['catrobat', 'zip'], true)) {
      $this->addFlash('sonata_flash_error', 'Invalid file type. Only .catrobat files are accepted.');

      return $this->redirectToRoute('admin_upload_project_list');
    }

    $flavor = (string) $request->request->get('flavor', Flavor::POCKETCODE);
    if (!in_array($flavor, Flavor::ALL, true)) {
      $flavor = Flavor::POCKETCODE;
    }

    $private = (bool) $request->request->get('private', false);

    try {
      $add_request = new AddProjectRequest(
        $user,
        $file,
        $request->getClientIp() ?? '127.0.0.1',
        $request->getLocale(),
        $flavor,
      );

      $project = $this->project_manager->addProject($add_request);

      if (null === $project) {
        $this->addFlash('sonata_flash_error', 'Upload failed: project could not be created.');

        return $this->redirectToRoute('admin_upload_project_list');
      }

      $project->setPrivate($private);
      $this->project_manager->save($project);

      $this->addFlash('sonata_flash_success', sprintf(
        'Project "%s" (ID: %s) uploaded successfully for user "%s".',
        $project->getName(),
        (string) $project->getId(),
        (string) $user->getUsername(),
      ));
    } catch (\Exception $e) {
      $this->logger->error('Admin project upload failed', [
        'error' => $e->getMessage(),
        'username' => $username,
      ]);
      $this->addFlash('sonata_flash_error', sprintf('Upload failed: %s', $e->getMessage()));
    }

    return $this->redirectToRoute('admin_upload_project_list');
  }
}
