<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Intl\Intl;
use Symfony\Component\HttpFoundation\Response;
use Catrobat\AppBundle\Entity\UserManager;

class TagExtensionController extends Controller
{

    /**
     * @Route("/tag/search/{q}", name="tag_search", requirements={"q":"\d+"})
     * @Method({"GET"})
     */
    public function tagSearchAction($q)
    {
        return $this->get('templating')->renderResponse('::tagSearch.html.twig', array('q' => $q));
    }

    /**
     * @Route("/tag/search/", name="empty_tag_search")
     * @Method({"GET"})
     */
    public function tagSearchNothingAction()
    {
        return $this->get('templating')->renderResponse('::search.html.twig', array('q' => null));
    }

    /**
     * @Route("/extension/search/{q}", name="extension_search", requirements={"q":".+"})
     * @Method({"GET"})
     */
    public function extensionSearchAction($q)
    {
        return $this->get('templating')->renderResponse('::extensionSearch.html.twig', array('q' => $q));
    }

    /**
     * @Route("/extension/search/", name="empty_extension_search")
     * @Method({"GET"})
     */
    public function extensionSearchNothingAction()
    {
        return $this->get('templating')->renderResponse('::search.html.twig', array('q' => null));
    }
}
