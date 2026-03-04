<?php

declare(strict_types=1);

namespace App\Admin\Moderation;

use App\DB\Entity\Moderation\ContentAppeal;
use App\DB\Entity\User\User;
use App\DB\Enum\AppealState;
use App\Moderation\AppealProcessor;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @phpstan-extends CRUDController<ContentAppeal>
 */
class AppealQueueController extends CRUDController
{
  public function __construct(
    private readonly AppealProcessor $appeal_processor,
  ) {
  }

  public function approveAppealAction(): RedirectResponse
  {
    /** @var ContentAppeal|null $appeal */
    $appeal = $this->admin->getSubject();
    if (null === $appeal) {
      $this->addFlash('sonata_flash_error', 'Appeal not found');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    if (AppealState::Pending->value !== $appeal->getState()) {
      $this->addFlash('warning', 'Appeal #'.$appeal->getId().' is already resolved');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /** @var User $admin */
    $admin = $this->getUser();
    $this->appeal_processor->approveAppeal($appeal, $admin);

    $this->addFlash('sonata_flash_success', 'Appeal #'.$appeal->getId().' approved - content restored');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }

  public function rejectAppealAction(): RedirectResponse
  {
    /** @var ContentAppeal|null $appeal */
    $appeal = $this->admin->getSubject();
    if (null === $appeal) {
      $this->addFlash('sonata_flash_error', 'Appeal not found');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    if (AppealState::Pending->value !== $appeal->getState()) {
      $this->addFlash('warning', 'Appeal #'.$appeal->getId().' is already resolved');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    /** @var User $admin */
    $admin = $this->getUser();
    $this->appeal_processor->rejectAppeal($appeal, $admin);

    $this->addFlash('sonata_flash_success', 'Appeal #'.$appeal->getId().' rejected');

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
