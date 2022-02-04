<?php

namespace App\Admin\Statistics\Translation\Controller;

use App\Commands\Helpers\CommandHelper;
use App\Entity\Translation\CommentMachineTranslation;
use App\Entity\Translation\ProjectMachineTranslation;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractMachineTranslationAdminController extends CRUDController
{
  protected const TYPE_PROJECT = 'TYPE_PROJECT';
  protected const TYPE_COMMENT = 'TYPE_COMMENT';

  protected string $type;

  private EntityManagerInterface $entity_manager;

  public function __construct(EntityManagerInterface $entity_manager)
  {
    $this->entity_manager = $entity_manager;
  }

  public function listAction(): Response
  {
    if (self::TYPE_PROJECT === $this->type) {
      $entity = ProjectMachineTranslation::class;
    } elseif (self::TYPE_COMMENT === $this->type) {
      $entity = CommentMachineTranslation::class;
    } else {
      $this->addFlash('sonata_flash_error', 'Invalid controller type');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('e.provider')
      ->addSelect('SUM(e.usage_per_month) as usage')
      ->from($entity, 'e')
      ->groupBy('e.provider')
      ;
    $provider_breakdown = $qb->getQuery()->getResult();

    return $this->renderWithExtraParams('Admin/Translation/admin_machine_translation.html.twig', [
      'action' => 'list',
      'trimUrl' => $this->admin->generateUrl('trim'),
      'providerBreakdown' => $provider_breakdown,
    ]);
  }

  public function trimAction(KernelInterface $kernel): Response
  {
    // TODO check permission

    $request = $this->getRequest();
    $days = $request->get('days');

    if (3 < strlen($days) || !ctype_digit($days) || 1 > $days) {
      $this->addFlash('sonata_flash_error', 'Days must be greater than 1');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    if (self::TYPE_PROJECT === $this->type) {
      $entity = '--only-project';
    } elseif (self::TYPE_COMMENT === $this->type) {
      $entity = '--only-comment';
    } else {
      $this->addFlash('sonata_flash_error', 'Invalid controller type');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:translation:trim-storage', '--older-than', $days, $entity],
      ['timeout' => 86400], '', null, $kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Command finished successfully');
    } else {
      $this->addFlash('sonata_flash_error', 'Error occurred running command!');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
