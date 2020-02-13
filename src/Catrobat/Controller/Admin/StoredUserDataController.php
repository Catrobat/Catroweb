<?php

namespace App\Catrobat\Controller\Admin;

use App\Entity\UserManager;
use App\Repository\CatroNotificationRepository;
use Sonata\AdminBundle\Controller\CRUDController;
use Doctrine\ORM\EntityManagerInterface;


/**
 * Class StoredUserDataController
 * @package App\Catrobat\Controller\Admin
 */
class StoredUserDataController extends CRUDController
{

  /**
   * @param int $id The id of the user which data should be shown
   * @param UserManager $user_manager
   * @param CatroNotificationRepository $notification_repo
   * @param EntityManagerInterface $entity_manager
   *
   * @return \Symfony\Component\HttpFoundation\Response
   */
  public function retrieveAction($id, UserManager $user_manager, CatroNotificationRepository $notification_repo,
                                  EntityManagerInterface $entity_manager)
  {
    $user = $user_manager->find($id);
    $catro_user_notifications = $notification_repo->findByUser($user, ['id' => 'DESC']);
    $query = $entity_manager->createQuery("SELECT cs FROM App\Entity\ClickStatistic cs WHERE cs.user='$id'");
    $click_statistics = $query->getResult();
    $query = $entity_manager->createQuery("SELECT hcs FROM App\Entity\HomepageClickStatistic hcs WHERE hcs.user='$id'");
    $homepage_click_statistics = $query->getResult();
    $query = $entity_manager->createQuery("SELECT n FROM App\Entity\Notification n WHERE n.user='$id'");
    $notifications = $query->getResult();
    $query = $entity_manager->createQuery("SELECT pir FROM App\Entity\ProgramInappropriateReport pir WHERE pir.reportingUser='$id'");
    $program_inappropriate_reports = $query->getResult();
    $query = $entity_manager->createQuery("SELECT pd FROM App\Entity\ProgramDownloads pd WHERE pd.user='$id'");
    $program_downloads = $query->getResult();
    $query = $entity_manager->createQuery("SELECT uc FROM App\Entity\UserComment uc WHERE uc.user='$id'");
    $user_comments = $query->getResult();
    $query = $entity_manager->createQuery("SELECT up FROM App\Entity\Program up WHERE up.user='$id'");
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
        'user_programs' => $user_programs
      ]);
  }
}