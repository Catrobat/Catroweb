<?php

declare(strict_types=1);

namespace App\Application\Controller\Search;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class SearchController extends AbstractController
{
  #[Route(path: '/search/{q}', name: 'search', requirements: ['q' => '.+'], methods: ['GET'])]
  #[Route(path: '/search/', name: 'empty_search', defaults: ['q' => null], methods: ['GET'])]
  public function search(?string $q = null): Response
  {
    return $this->render('Search/SearchPage.html.twig', ['q' => $q]);
  }
}
