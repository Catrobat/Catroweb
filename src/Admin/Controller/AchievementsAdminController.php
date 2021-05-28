<?php

namespace App\Admin\Controller;

use App\Manager\AchievementManager;
use Exception;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AchievementsAdminController extends CRUDController
{
  protected AchievementManager $achievement_manager;

  public function __construct(AchievementManager $achievement_manager)
  {
    $this->achievement_manager = $achievement_manager;
  }

  public function listAction(Request $request = null): Response
  {
    $achievements = $this->achievement_manager->findAllAchievements();

    $numberOfUserAchievements = [];
    foreach ($achievements as $achievement) {
      $id = $achievement->getId();
      $numberOfUserAchievements[$id] = $this->achievement_manager->countUserAchievementsOfAchievement($id);
    }

    return $this->renderWithExtraParams('Admin/admin_achievements.html.twig', [
      'achievements' => $achievements,
      'numberOfUserAchievements' => $numberOfUserAchievements,
      'updateAchievementsUrl' => $this->admin->generateUrl('update_achievements'),
    ]);
  }

  /**
   * @throws Exception
   */
  public function updateAchievementsAction(KernelInterface $kernel): RedirectResponse
  {
    if (!$this->admin->isGranted('ACHIEVEMENTS')) {
      throw new AccessDeniedException();
    }

    $application = new Application($kernel);
    $application->setAutoExit(false);
    $result = $application->run(new ArrayInput(['command' => 'catrobat:update:achievements']), new NullOutput());

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Achievements have been successfully updated');
    } else {
      $this->addFlash('sonata_flash_error', 'Updating achievements failed!');
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
