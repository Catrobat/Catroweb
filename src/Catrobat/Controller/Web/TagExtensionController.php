<?php

namespace App\Catrobat\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Twig\Error\Error;


/**
 * Class TagExtensionController
 * @package App\Catrobat\Controller\Web
 */
class TagExtensionController extends AbstractController
{

  /**
   * @Route("/tag/search/{q}", name="tag_search", requirements={"q":"\d+"}, methods={"GET"})
   *
   * @param $q
   *
   * @return Response
   * @throws Error
   */
  public function tagSearchAction($q)
  {
    return $this->get('templating')->renderResponse('Search/tagSearch.html.twig', ['q' => $q]);
  }


  /**
   * @Route("/tag/search/", name="empty_tag_search", methods={"GET"})
   *
   * @return Response
   * @throws Error
   */
  public function tagSearchNothingAction()
  {
    return $this->get('templating')->renderResponse('Search/search.html.twig', ['q' => null]);
  }


  /**
   * @Route("/extension/search/{q}", name="extension_search", requirements={"q":".+"}, methods={"GET"})
   *
   * @param $q
   *
   * @return Response
   * @throws Error
   */
  public function extensionSearchAction($q)
  {
    return $this->get('templating')->renderResponse('Search/extensionSearch.html.twig', ['q' => $q]);
  }


  /**
   * @Route("/extension/search/", name="empty_extension_search", methods={"GET"})
   *
   * @return Response
   * @throws Error
   */
  public function extensionSearchNothingAction()
  {
    return $this->get('templating')->renderResponse('Search/search.html.twig', ['q' => null]);
  }
}
