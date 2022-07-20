<?php

namespace App\Admin\Comments\ReportedComments;

use App\DB\Entity\Project\ProgramInappropriateReport;
use App\DB\Entity\User\Comment\UserComment;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ReportedCommentsController extends CRUDController
{
  public function __construct(
    private readonly EntityManagerInterface $entity_manager
  ) {
  }

  public function unreportProgramAction(): RedirectResponse
  {
    /** @var ProgramInappropriateReport|null $object */
    $object = $this->admin->getSubject();
    if (null === $object) {
      throw new NotFoundHttpException();
    }
    $program = $object->getProgram();
    $program->setVisible(true);
    $program->setApproved(true);
    $object->setState(3);
    $this->admin->update($object);
    $this->addFlash('sonata_flash_success', 'Program '.$object->getId().' is no longer reported');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

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
