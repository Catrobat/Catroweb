<?php

declare(strict_types=1);

namespace App\Application\Controller\Test;

use App\Admin\System\FeatureFlag\FeatureFlagManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TestFeatureFlagController extends AbstractController
{
  public function __construct(protected FeatureFlagManager $manager)
  {
  }

  #[Route(path: '/featureflag/test', name: 'test_flag', methods: ['GET'])]
  public function testFlag(): Response
  {
    return $this->render('Test/FeatureFlagPage.html.twig', ['enabled' => $this->manager->isEnabled('Test-Flag')]);
  }
}
