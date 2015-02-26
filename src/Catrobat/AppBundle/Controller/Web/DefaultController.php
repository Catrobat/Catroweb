<?php

namespace Catrobat\AppBundle\Controller\Web;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Catrobat\AppBundle\Services\FeaturedImageRepository;
use Catrobat\AppBundle\Entity\FeaturedRepository;

class DefaultController extends Controller
{
  public function headerAction()
  {
    return $this->get("templating")->renderResponse(':Default:header.html.twig');
  }

  public function headerLogoAction()
  {
    return $this->get("templating")->renderResponse(':Default:headerLogo.html.twig');
  }

  public function footerAction()
  {
    return $this->get("templating")->renderResponse(':Default:footer.html.twig');
  }

  /**
   * @Route("/", name="catrobat_web_index")
   * @Method({"GET"})
   */
  public function indexAction()
  {
      /* @var $image_repository FeaturedImageRepository */
      $image_repository = $this->get('featuredimagerepository');
      /* @var $repository FeaturedRepository */
      $repository = $this->get('featuredrepository');

      $programs = $repository->getFeaturedPrograms(5, 0);

      $featured = array();
      foreach ($programs as $program)
      {
          $info = array();
          if ($program->getProgram() !== null)
          {
              $info['url'] = $this->generateUrl('catrobat_web_program', array('id' => $program->getProgram()->getId()));
          }
          else 
          {
              $info['url'] = $program->getUrl();
          }
          $info['image'] = $image_repository->getWebPath($program->getId(), $program->getImageType());;
          $featured[] = $info;
      }
    return $this->get("templating")->renderResponse('::index.html.twig', array("featured" => $featured));
  }

  /**
   * @Route("/program/{id}", name="catrobat_web_program", requirements={"id":"\d+"})
   * @Route("/details/{id}", name="catrobat_web_detail", requirements={"id":"\d+"})
   * @Method({"GET"})
   */
  public function programAction($id)
  {
    //IMPORTANT: if you change the route '/program' .. also adapt it in ProgramLoader.js (variable: 'program_link')

    $program = $this->get("programmanager")->find($id);

    if (!$program) {
      throw $this->createNotFoundException('Unable to find Project entity.');
    }
    return $this->get("templating")->renderResponse('::program.html.twig', array("program" => $program));
  }

  /**
   * @Route("/search/{q}", name="catrobat_web_search", requirements={"q":".+"})
   * @Method({"GET"})
   */
  public function searchAction($q)
  {
    return $this->get("templating")->renderResponse('::search.html.twig', array("q" => $q));
  }

  /**
   * @Route("/search/", name="catrobat_web_search_nothing")
   * @Method({"GET"})
   */
  public function searchNothingAction()
  {
    return $this->get("templating")->renderResponse('::search.html.twig', array("q" => null));
  }

  /**
   * @Route("/profile/{id}", name="catrobat_web_profile", requirements={"id":"\d+"})
   * @Method({"GET"})
   */
  public function profileAction($id)
  {
    $profile = $id;

    return $this->get("templating")->renderResponse('::profile.html.twig', array("profile" => $profile));
  }

  /**
   * @Route("/termsOfUse", name="catrobat_web_termsOfUse")
   * @Method({"GET"})
   */
  public function termsOfUseAction()
  {
    return $this->get("templating")->renderResponse('::termsOfUse.html.twig');
  }

  /**
   * @Route("/licenseToPlay", name="catrobat_web_licenseToPlay")
   * @Method({"GET"})
   */
  public function licenseToPlayAction()
  {
    return $this->get("templating")->renderResponse('::licenseToPlay.html.twig');
  }
}
