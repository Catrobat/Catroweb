<?php

namespace App\Catrobat\Controller\Web;

use App\Entity\Program;
use App\Entity\StarterCategory;
use App\Catrobat\Services\ScreenshotRepository;
use Doctrine\DBAL\Types\GuidType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Twig\Error\Error;


/**
 * Class TutorialController
 * @package App\Catrobat\Controller\Web
 */
class TutorialController extends Controller
{

  /**
   * @Route("/help", name="catrobat_web_help", methods={"GET"})
   *
   * @return Response
   * @throws Error
   */
  public function helpAction()
  {
    return $this->get('templating')->renderResponse('help/help.html.twig');
  }


  /**
   * @Route("/step-by-step/{page}", name="catrobat_web_step_by_step", defaults={"page" = 1},
   *                                requirements={"page":"\d+"}, methods={"GET"})
   * @Route("/stepByStep/{page}", name="catrobat_web_stepByStep", defaults={"page" = 1},
   *                                requirements={"page":"\d+"}, methods={"GET"})
   *
   * @return Response
   * @throws Error
   */
  public function stepByStepAction()
  {
    return $this->get('templating')->renderResponse('help/stepByStep.html.twig', []);
  }


  /**
   * @Route("/tutorialcards/{page}", name="catrobat_web_tutorialcards", defaults={"page" = -1},
   *                                 requirements={"page":"\d+"}, methods={"GET"})
   *
   * @param $page
   *
   * @return Response
   * @throws Error
   */
  public function tutorialCardsAction($page)
  {
    $cards_num = 12;

    if ($page > $cards_num)
    {
      throw $this->createNotFoundException('Unable to find tutorialcard.');
    }

    if ($page == -1)
    {
      return $this->get('templating')->renderResponse('help/tutorialcards.html.twig', ['count' => $cards_num]);
    }

    $blocks = $this->generateBlocks($page);

    $example_link = $this->setExampleLink($page);

    return $this->get('templating')->renderResponse('help/tutorialcard.html.twig', [
      'page'         => $page,
      'blocks'       => $blocks,
      'example_link' => $example_link,
    ]);
  }


  /**
   * @Route("/starter-programs", name="catrobat_web_starter", methods={"GET"})
   *
   * @return Response
   * @throws Error
   */
  public function starterProgramsAction()
  {
    /**
    * @var $categories StarterCategory
    */

    $em = $this->getDoctrine()->getManager();

    $categories = $em->getRepository('App\Entity\StarterCategory')->findBy([], ['order' => 'asc']);

    $categories_twig = $this->generateCategoryArray($categories);

    return $this->get('templating')->renderResponse('help/starterPrograms.html.twig', [
      'categories' => $categories_twig,
    ]);
  }


  /**
   * @Route("/category-programs/{id}", name="catrobat_web_category_programs", methods={"GET"})
   *
   * @param Request $request
   * @param GuidType  $id
   *
   * @return JsonResponse
   */
  public function categoryProgramsAction(Request $request, $id)
  {
    /**
    * @var $program Program
    */

    $em = $this->getDoctrine()->getManager();
    $programs = $em->getRepository('App\Entity\Program')->findBy(['category' => $id]);

    $screenshot_repository = $this->get('screenshotrepository');

    $retArray = $this->receiveCategoryPrograms($request, $programs, $screenshot_repository);

    $retArray['CatrobatInformation'] = [
      'BaseUrl'           => ($request->isSecure() ? 'https://' : 'http://') . $request->getHttpHost() . '/',
      'TotalProjects'     => count($programs),
      'ProjectsExtension' => '.catrobat',
    ];

    return JsonResponse::create($retArray);
  }


  /**
   * @Route("/pocket-game-jam", name="catrobat_web_game_jam", methods={"GET"})
   *
   * @return Response
   * @throws Error
   */
  public function gameJamAction()
  {
    return $this->get('templating')->renderResponse('help/gamejam.html.twig');
  }


  /**
   * @param $page
   *
   * @return array
   */
  private function generateBlocks($page)
  {
    $blocks = [];
    $blocks[0] = ['block' => 'one', 'images' => 1];
    $blocks[1] = ['block' => 'two', 'images' => 1];

    switch ($page)
    {
      case 2:
      case 3:
      case 10:
        $blocks[2] = ['block' => 'three', 'images' => 1];
        break;

      case 9:
        $blocks[0] = ['block' => 'one', 'images' => 2];
        $blocks[2] = ['block' => 'three', 'images' => 1];
        break;

      case 11:
        $blocks[1] = ['block' => 'two', 'images' => 2];
        break;

      case 12:
        $blocks[1] = ['block' => 'two', 'images' => 3];
        break;
    }

    return $blocks;
  }


  /**
   * @param $page
   *
   * @return null|string
   */
  private function setExampleLink($page)
  {
    $example_link = null;
    switch ($page)
    {
      case 1:
        $example_link = $this->generateUrl('program', ['id' => 3983, 'flavor' => 'pocketcode']);
        break;
      case 2:
        $example_link = $this->generateUrl('program', ['id' => 3984, 'flavor' => 'pocketcode']);
        break;
      case 3:
        $example_link = $this->generateUrl('program', ['id' => 3985, 'flavor' => 'pocketcode']);
        break;
      case 4:
        $example_link = $this->generateUrl('program', ['id' => 3986, 'flavor' => 'pocketcode']);
        break;
      case 5:
        $example_link = $this->generateUrl('program', ['id' => 3987, 'flavor' => 'pocketcode']);
        break;
      case 6:
        $example_link = $this->generateUrl('program', ['id' => 3988, 'flavor' => 'pocketcode']);
        break;
      case 7:
        $example_link = $this->generateUrl('program', ['id' => 3990, 'flavor' => 'pocketcode']);
        break;
      case 8:
        $example_link = $this->generateUrl('program', ['id' => 3991, 'flavor' => 'pocketcode']);
        break;
      case 9:
        $example_link = $this->generateUrl('program', ['id' => 3992, 'flavor' => 'pocketcode']);
        break;
      case 10:
        $example_link = $this->generateUrl('program', ['id' => 3979, 'flavor' => 'pocketcode']);
        break;
      case 11:
        $example_link = $this->generateUrl('program', ['id' => 3981, 'flavor' => 'pocketcode']);
        break;
      case 12:
        $example_link = $this->generateUrl('program', ['id' => 3982, 'flavor' => 'pocketcode']);
        break;
    }

    return $example_link;
  }


  /**
   * @param $categories
   *
   * @return array
   */
  private function generateCategoryArray($categories)
  {
    /**
     * @var $category StarterCategory
     */

    $categories_twig = [];

    foreach ($categories as $category)
    {
      $categories_twig[] = [
        'id'    => $category->getId(),
        'alias' => $category->getAlias(),
      ];
    }

    return $categories_twig;
  }

  /**
   * @param Request                 $request
   * @param array                   $programs
   * @param ScreenshotRepository    $screenshot_repository
   *
   * @return array
   */
  private function receiveCategoryPrograms(Request $request, $programs, $screenshot_repository)
  {
    /**
     * @var $program Program
     */

    $retArray = [
      'CatrobatProjects' => [],
    ];

    foreach ($programs as $program)
    {
      $retArray['CatrobatProjects'][] = [
        'ProjectId'       => $program->getId(),
        'ProjectName'     => $program->getName(),
        'Downloads'       => $program->getDownloads(),
        'ScreenshotSmall' => $screenshot_repository->getThumbnailWebPath($program->getId()),
        'ProjectUrl'      => ltrim($this->generateUrl('program', [
          'flavor' => $request->get('flavor'),
          'id'     => $program->getId(),
        ]), '/'),
      ];
    }

    return $retArray;
  }
}
