<?php

declare(strict_types=1);

namespace App\Admin\Block;

use Doctrine\ORM\EntityManagerInterface;
use Sonata\AdminBundle\Admin\Pool;
use Sonata\BlockBundle\Block\BlockContextInterface;
use Sonata\BlockBundle\Block\Service\AbstractBlockService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Twig\Environment;

/**
 * Dashboard stats block that uses a real COUNT(*) query instead of
 * SimplePager's capped estimate (which tops out at limit+1).
 */
final class AdminCountBlockService extends AbstractBlockService
{
  public function __construct(
    Environment $twig,
    private readonly Pool $pool,
    private readonly EntityManagerInterface $entity_manager,
  ) {
    parent::__construct($twig);
  }

  public function execute(BlockContextInterface $blockContext, ?Response $response = null): Response
  {
    $admin = $this->pool->getAdminByAdminCode($blockContext->getSetting('code'));
    $class = $admin->getClass();

    $count = (int) $this->entity_manager->createQueryBuilder()
      ->select('COUNT(e.id)')
      ->from($class, 'e')
      ->getQuery()
      ->getSingleScalarResult()
    ;

    return $this->renderResponse($blockContext->getTemplate(), [
      'block' => $blockContext->getBlock(),
      'settings' => $blockContext->getSettings(),
      'admin' => $admin,
      'count' => $count,
    ], $response);
  }

  public function configureSettings(OptionsResolver $resolver): void
  {
    $resolver->setDefaults([
      'icon' => 'fas fa-chart-line',
      'text' => 'Statistics',
      'translation_domain' => null,
      'color' => 'bg-aqua',
      'code' => false,
      'template' => 'Admin/Block/block_count.html.twig',
    ]);
  }
}
