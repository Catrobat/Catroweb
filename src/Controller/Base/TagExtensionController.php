<?php

namespace App\Controller\Base;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TagExtensionController extends AbstractController
{
  /**
   * @Route("/tag/search/{q}", name="tag_search", methods={"GET"})
   */
  public function tagSearchAction(string $q): Response
  {
    return $this->render('Search/tagSearch.html.twig', ['q' => $q]);
  }

  /**
   * @Route("/extension/search/{q}", name="extension_search", methods={"GET"})
   */
  public function extensionSearchAction(string $q): Response
  {
    return $this->render('Search/extensionSearch.html.twig', ['q' => $q]);
  }
}
