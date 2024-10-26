<?php

declare(strict_types=1);

namespace App\Application\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class TagExtensionController extends AbstractController
{
  #[Route(path: '/tag/search/{q}', name: 'tag_search', methods: ['GET'])]
  public function tagSearch(string $q): Response
  {
    return $this->render('Search/TagSearchPage.html.twig', ['q' => $q]);
  }

  #[Route(path: '/extension/search/{q}', name: 'extension_search', methods: ['GET'])]
  public function extensionSearch(string $q): Response
  {
    return $this->render('Search/ExtensionSearchPage.html.twig', ['q' => $q]);
  }
}
