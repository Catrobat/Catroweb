<?php

declare(strict_types=1);

namespace App\Admin\Comments\ReportedComments;

use App\DB\Entity\Project\ProgramInappropriateReport;
use App\DB\Entity\User\Comment\UserComment;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Exception\LockException;
use Sonata\AdminBundle\Exception\ModelManagerThrowable;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @phpstan-extends CRUDController<ProgramInappropriateReport|UserComment>
 */
class ReportedCommentsController extends CRUDController
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
  ) {
  }

  /**
   * @throws LockException
   * @throws ModelManagerThrowable
   */
  public function unreportProjectAction(): RedirectResponse
  {
    /** @var ProgramInappropriateReport $report */
    $report = $this->admin->getSubject();
    $project = $report->getProgram();
    $project->setVisible(true);
    $project->setApproved(true);

    $report->setState(3);
    $this->admin->update($report);
    $id = $report->getId();
    if (null !== $id) {
      $this->addFlash('sonata_flash_success', "Project {$id} is no longer reported");
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  /**
   * @throws LockException
   * @throws ModelManagerThrowable
   */
  public function unreportCommentAction(): RedirectResponse
  {
    /* @var $object UserComment */
    $object = $this->admin->getSubject();
    $object->setIsReported(false);

    $this->admin->update($object);
    $this->addFlash('sonata_flash_success', 'Comment '.$object->getId().' is no longer reported');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function deleteCommentAction(): RedirectResponse
  {
    /* @var $object UserComment */
    $object = $this->admin->getSubject();
    $comment = $this->entity_manager->getRepository(UserComment::class)->find($object->getId());
    if (null === $comment) {
      throw $this->createNotFoundException('No comment found for this id '.$object->getId());
    }

    $this->entity_manager->remove($comment);
    $this->entity_manager->flush();
    $this->addFlash('sonata_flash_success', 'Comment '.$object->getId().' deleted');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
