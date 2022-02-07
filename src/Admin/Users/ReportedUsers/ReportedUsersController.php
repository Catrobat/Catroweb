<?php

namespace App\Admin\Users\ReportedUsers;

use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class ReportedUsersController extends CRUDController
{
  public function createUrlProgramsAction(): RedirectResponse
  {
    $filter = [
      'reportedUser' => [
        'value' => $this->admin->getSubject()->getId(),
      ],
    ];

    return new RedirectResponse($this->container->get('router')->generate(
            'admin_reported_projects_list', ['filter' => $filter])
        );
  }

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
