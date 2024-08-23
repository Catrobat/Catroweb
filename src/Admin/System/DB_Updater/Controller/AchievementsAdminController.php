<?php

declare(strict_types=1);

namespace App\Admin\System\DB_Updater\Controller;

use App\DB\Entity\User\Achievements\Achievement;
use App\System\Commands\Helpers\CommandHelper;
use App\User\Achievements\AchievementManager;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @phpstan-extends CRUDController<Achievement>
 */
class AchievementsAdminController extends CRUDController
{
  public function __construct(
    protected AchievementManager $achievement_manager,
    protected KernelInterface $kernel
  ) {
  }

  #[\Override]
  public function listAction(Request $request): Response
  {
    $achievements = $this->achievement_manager->findAllAchievements();
    $numberOfUserAchievements = [];
    foreach ($achievements as $achievement) {
      $id = $achievement->getId();
      $numberOfUserAchievements[$id] = $this->achievement_manager->countUserAchievementsOfAchievement($id);
    }

    return $this->renderWithExtraParams('Admin/SystemManagement/DbUpdater/achievements.html.twig', [
      'action' => 'update_achievements',
      'numberOfUserAchievements' => $numberOfUserAchievements,
      'updateAchievementsUrl' => $this->admin->generateUrl('update_achievements'),
    ]);
  }

  /**
   * @throws \Exception
   */
  public function updateAchievementsAction(): RedirectResponse
  {
    if (!$this->admin->isGranted('ACHIEVEMENTS')) {
      throw new AccessDeniedException();
    }

    $output = new BufferedOutput();
    $result = CommandHelper::executeShellCommand(
      ['bin/console', 'catrobat:update:achievements'], ['timeout' => 86400], '', $output, $this->kernel
    );

    if (0 === $result) {
      $this->addFlash('sonata_flash_success', 'Achievements have been successfully updated');
    } else {
      $this->addFlash('sonata_flash_error', "Updating achievements failed!\n".$output->fetch());
    }

    return new RedirectResponse($this->admin->generateUrl('list'));
  }
}
