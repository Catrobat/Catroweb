<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\UserManager;
use App\Repository\CatroNotificationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\Response;

class StoredUserDataController extends CRUDController
{
  /**
   * @param string $id The id of the user which data should be shown
   */
  public function retrieveAction(string $id, UserManager $user_manager, CatroNotificationRepository $notification_repo,
                                  EntityManagerInterface $entity_manager): Response
  {
    $user = $user_manager->find($id);
    $catro_user_notifications = $notification_repo->findBy(['user' => $user], ['id' => 'DESC']);
    $query = $entity_manager->createQuery(sprintf('SELECT cs FROM App\Entity\ClickStatistic cs WHERE cs.user=\'%s\'', $id));
    $click_statistics = $query->getResult();
    $query = $entity_manager->createQuery(sprintf('SELECT hcs FROM App\Entity\HomepageClickStatistic hcs WHERE hcs.user=\'%s\'', $id));
    $homepage_click_statistics = $query->getResult();
    $query = $entity_manager->createQuery(sprintf('SELECT n FROM App\Entity\Notification n WHERE n.user=\'%s\'', $id));
    $notifications = $query->getResult();
    $query = $entity_manager->createQuery(sprintf('SELECT pir FROM App\Entity\ProgramInappropriateReport pir WHERE pir.reportingUser=\'%s\'', $id));
    $program_inappropriate_reports = $query->getResult();
    $query = $entity_manager->createQuery(sprintf('SELECT pd FROM App\Entity\ProgramDownloads pd WHERE pd.user=\'%s\'', $id));
    $program_downloads = $query->getResult();
    $query = $entity_manager->createQuery(sprintf('SELECT uc FROM App\Entity\UserComment uc WHERE uc.user=\'%s\'', $id));
    $user_comments = $query->getResult();
    $query = $entity_manager->createQuery(sprintf('SELECT up FROM App\Entity\Program up WHERE up.user=\'%s\'', $id));
    $user_programs = $query->getResult();

    return $this->renderWithExtraParams('Admin/CRUD/list__action_show_user_data.html.twig',
      [
        'user' => $user,
        'catro_user_notifications' => $catro_user_notifications,
        'click_statistics' => $click_statistics,
        'homepage_click_statistics' => $homepage_click_statistics,
        'notifications' => $notifications,
        'program_inappropriate_reports' => $program_inappropriate_reports,
        'program_downloads' => $program_downloads,
        'user_comments' => $user_comments,
        'user_programs' => $user_programs,
      ]);
  }
}
