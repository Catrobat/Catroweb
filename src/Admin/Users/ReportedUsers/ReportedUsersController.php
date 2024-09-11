<?php

declare(strict_types=1);

namespace App\Admin\Users\ReportedUsers;

use App\DB\Entity\User\User;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

/**
 * @phpstan-extends CRUDController<User>
 */
class ReportedUsersController extends CRUDController
{
  /**
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function createUrlProjectsAction(): RedirectResponse
  {
    $filter = [
      'reported_user' => [
        'value' => $this->admin->getSubject()->getId(),
      ],
    ];

    return new RedirectResponse($this->container->get('router')->generate(
      'admin_reported_projects_list', ['filter' => $filter])
    );
  }

  /**
   * @throws ContainerExceptionInterface
   * @throws NotFoundExceptionInterface
   */
  public function createUrlCommentsAction(): RedirectResponse
  {
    $filter = [
      'user' => [
        'value' => $this->admin->getSubject()->getId(),
      ],
    ];

    return new RedirectResponse($this->container->get('router')->generate(
      'admin_report_list', ['filter' => $filter])
    );
  }
}
