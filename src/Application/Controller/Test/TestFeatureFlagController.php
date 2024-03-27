<?php

namespace App\Application\Controller\Test;

use App\Admin\Tools\FeatureFlag\FeatureFlagManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TestFeatureFlagController extends AbstractController
{
  public function __construct(protected FeatureFlagManager $manager)
  {
  }

  #[Route(path: '/featureflag/test', name: 'test_flag', methods: ['GET'])]
  public function testFlagAction(): Response
  {
    return $this->render('Admin/Tools/test_feature_flag.html.twig', ['enabled' => $this->manager->isEnabled('Test-Flag')]);
  }
}
