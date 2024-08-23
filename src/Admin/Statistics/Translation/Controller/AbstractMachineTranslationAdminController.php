<?php

declare(strict_types=1);

namespace App\Admin\Statistics\Translation\Controller;

use App\DB\Entity\Translation\CommentMachineTranslation;
use App\DB\Entity\Translation\ProjectMachineTranslation;
use App\System\Commands\Helpers\CommandHelper;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Bridge\Exporter\AdminExporter;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @phpstan-extends CRUDController<ProjectMachineTranslation|CommentMachineTranslation>
 */
abstract class AbstractMachineTranslationAdminController extends CRUDController
{
  protected const TYPE_PROJECT = 'TYPE_PROJECT';

  protected const TYPE_COMMENT = 'TYPE_COMMENT';

  protected string $type;

  public function __construct(
    private readonly EntityManagerInterface $entity_manager,
    private readonly KernelInterface $kernel
  ) {
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    if (self::TYPE_PROJECT === $this->type) {
      $entity = ProjectMachineTranslation::class;
    } elseif (self::TYPE_COMMENT === $this->type) {
      $entity = CommentMachineTranslation::class;
    } else {
      $this->addFlash('sonata_flash_error', 'Invalid controller type');

      return new RedirectResponse($this->admin->generateUrl('list'));
    }

    $this->assertObjectExists($request);

    $this->admin->checkAccess('list');

    $preResponse = $this->preList($request);
    if ($preResponse instanceof Response) {
      return $preResponse;
    }

    $listMode = $request->query->get('_list_mode');
    if (\is_string($listMode)) {
      $this->admin->setListMode($listMode);
    }

    $datagrid = $this->admin->getDatagrid();
    $formView = $datagrid->getForm()->createView();

    // set the theme for the current Admin Form
    $this->setFormTheme($formView, $this->admin->getFilterTheme());

    if ($this->container->has('sonata.admin.admin_exporter')) {
      $exporter = $this->container->get('sonata.admin.admin_exporter');
      \assert($exporter instanceof AdminExporter);
      $exportFormats = $exporter->getAvailableFormats($this->admin);
    }

    $qb = $this->entity_manager->createQueryBuilder();
    $qb->select('e.provider')
      ->addSelect('SUM(e.usage_per_month) as usage')
      ->from($entity, 'e')
      ->groupBy('e.provider')
    ;
    $provider_breakdown = $qb->getQuery()->getResult();

    return $this->render('Admin/Statistics/MachineTranslation.html.twig', [
      'action' => 'list',
      'trimUrl' => $this->admin->generateUrl('trim'),
      'providerBreakdown' => $provider_breakdown,
      'form' => $formView,
      'datagrid' => $datagrid,
      'csrf_token' => $this->getCsrfToken('sonata.batch'),
      'export_formats' => $exportFormats ?? $this->admin->getExportFormats(),
    ]);
  }

  public function trimAction(Request $request): Response
  {
    // TODO check permission

    $days = (string) $request->query->get('days');

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
      ['timeout' => 86400], '', null, $this->kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Command finished successfully');
    } else {
      $this->addFlash('sonata_flash_error', 'Error occurred running command!');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
