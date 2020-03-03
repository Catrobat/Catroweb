<?php

namespace App\Catrobat\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error;

/**
 * Class TagExtensionController.
 */
class TagExtensionController extends AbstractController
{
  /**
   * @Route("/tag/search/{q}", name="tag_search", requirements={"q": "\d+"}, methods={"GET"})
   *
   * @param $q
   *
   * @throws Error
   *
   * @return Response
   */
  public function tagSearchAction($q)
  {
    return $this->render('Search/tagSearch.html.twig', ['q' => $q]);
  }

  /**
   * @Route("/tag/search/", name="empty_tag_search", methods={"GET"})
   *
   * @throws Error
   *
   * @return Response
   */
  public function tagSearchNothingAction()
  {
    return $this->render('Search/search.html.twig', ['q' => null]);
  }

  /**
   * @Route("/extension/search/{q}", name="extension_search", requirements={"q": ".+"}, methods={"GET"})
   *
   * @param $q
   *
   * @throws Error
   *
   * @return Response
   */
  public function extensionSearchAction($q)
  {
    return $this->render('Search/extensionSearch.html.twig', ['q' => $q]);
  }

  /**
   * @Route("/extension/search/", name="empty_extension_search", methods={"GET"})
   *
   * @throws Error
   *
   * @return Response
   */
  public function extensionSearchNothingAction()
  {
    return $this->render('Search/search.html.twig', ['q' => null]);
  }
}
