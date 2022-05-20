<?php

namespace App\Admin\Users\UserDataReport;

use App\DB\EntityRepository\User\Notification\NotificationRepository;
use App\User\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

class UserDataReportController extends CRUDController
{
  public function __construct(protected UserManager $user_manager, protected NotificationRepository $notification_repository, protected EntityManagerInterface $entity_manager)
  {
  }

  /**
   * @param string $id The id of the user which data should be shown
   */
  public function retrieveAction(string $id): Response
  {
    $user = $this->user_manager->find($id);
    $notifications = $this->getUserNotifications($id);
    $program_inappropriate_reports = $this->getReportedProjects($id);
    $user_comments = $this->getUserComments($id);
    $user_programs = $this->getUserProjects($id);

    return $this->renderWithExtraParams('Admin/CRUD/list__action_show_user_data.html.twig',
      [
        'user' => $user,
        'notifications' => $notifications,
        'program_inappropriate_reports' => $program_inappropriate_reports,
        'user_comments' => $user_comments,
        'user_programs' => $user_programs,
      ]);
  }

  protected function getUserNotifications(string $user_id): array
  {
    return $this->notification_repository->findBy(['user' => $user_id], ['id' => 'DESC']);
  }

  /**
   * @return mixed
   */
  protected function getReportedProjects(string $user_id)
  {
    return $this->entity_manager
      ->createQuery(sprintf('SELECT pir FROM App\DB\Entity\Project\ProgramInappropriateReport pir WHERE pir.reportingUser=\'%s\'', $user_id))
      ->getResult()
    ;
  }

  /**
   * @return mixed
   */
  protected function getUserComments(string $user_id)
  {
    return $this->entity_manager
      ->createQuery(sprintf('SELECT uc FROM App\DB\Entity\User\Comment\UserComment uc WHERE uc.user=\'%s\'', $user_id))
      ->getResult()
    ;
  }

  /**
   * @return mixed
   */
  protected function getUserProjects(string $user_id)
  {
    return $this->entity_manager
      ->createQuery(sprintf('SELECT up FROM App\DB\Entity\Project\Program up WHERE up.user=\'%s\'', $user_id))
      ->getResult()
    ;
  }
}
