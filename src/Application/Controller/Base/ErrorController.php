<?php

namespace App\Application\Controller\Base;

use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\Storage\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ErrorController extends AbstractController
{
  public function __construct(protected ImageRepository $image_repository, protected FeaturedRepository $featured_repository)
  {
  }

  #[Route(path: '/error/{status_code}', name: 'error', methods: ['GET'])]
  public function errorPage(Request $request): Response
  {
    $status_code = $request->attributes->get('status_code', 500);
    $flavor = $request->attributes->get('flavor');

    return $this->render('Default/error.html.twig', [
      'status_code' => $status_code,
      'flavor' => $flavor,
    ]);
  }
}
