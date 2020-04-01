<?php

namespace App\Catrobat\Controller\Web;

use App\Catrobat\Services\ScreenshotRepository;
use App\Entity\Program;
use App\Entity\StarterCategory;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TutorialController extends AbstractController
{
  /**
   * @Route("/help", name="catrobat_web_help", methods={"GET"})
   */
  public function helpAction(): Response
  {
    return $this->render('help/help.html.twig');
  }

  /**
   * @Route("/step-by-step/{page}", name="catrobat_web_step_by_step", defaults={"page": 1}, requirements={"page": "\d+"}, methods={"GET"})
   * @Route("/stepByStep/{page}", name="catrobat_web_stepByStep", defaults={"page": 1}, requirements={"page": "\d+"}, methods={"GET"})
   */
  public function stepByStepAction(): Response
  {
    return $this->render('help/stepByStep.html.twig', []);
  }

  /**
   * @Route("/tutorialcards/{page}", name="catrobat_web_tutorialcards", defaults={"page": -1},
   * requirements={"page": "\d+"}, methods={"GET"})
   */
  public function tutorialCardsAction(int $page): Response
  {
    $cards_num = 12;

    if ($page > $cards_num)
    {
      throw $this->createNotFoundException('Unable to find tutorialcard.');
    }

    if (-1 == $page)
    {
      return $this->render('help/tutorialcards.html.twig', ['count' => $cards_num]);
    }

    $blocks = $this->generateBlocks($page);

    $example_link = $this->setExampleLink($page);

    return $this->render('help/tutorialcard.html.twig', [
      'page' => $page,
      'blocks' => $blocks,
      'example_link' => $example_link,
    ]);
  }

  /**
   * @Route("/starter-project/", name="catrobat_web_starter", methods={"GET"})
   */
  public function starterProgramsAction(): Response
  {
    $em = $this->getDoctrine()->getManager();
    $categories = $em->getRepository(StarterCategory::class)->findBy([], ['order' => 'asc']);

    $categories_twig = $this->generateCategoryArray($categories);

    return $this->render('help/starterPrograms.html.twig', [
      'categories' => $categories_twig,
    ]);
  }

  /**
   * @Route("/category-project/{id}", name="catrobat_web_category_programs", methods={"GET"})
   */
  public function categoryProgramsAction(Request $request, string $id, ScreenshotRepository $screenshot_repository): JsonResponse
  {
    $em = $this->getDoctrine()->getManager();
    $programs = $em->getRepository(Program::class)->findBy(['category' => $id]);

    $retArray = $this->receiveCategoryPrograms($request, $programs, $screenshot_repository);

    $retArray['CatrobatInformation'] = [
      'BaseUrl' => $request->getSchemeAndHttpHost().'/',
      'TotalProjects' => is_countable($programs) ? count($programs) : 0,
      'ProjectsExtension' => '.catrobat',
    ];

    return JsonResponse::create($retArray);
  }

  /**
   * @Route("/pocket-game-jam", name="catrobat_web_game_jam", methods={"GET"})
   */
  public function gameJamAction(): Response
  {
    return $this->render('help/gamejam.html.twig');
  }

  /**
   * @param mixed $page
   */
  private function generateBlocks($page): array
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

  private function setExampleLink(int $page): ?string
  {
    $example_link = null;
    switch ($page)
    {
      case 1:
        $example_link = $this->generateUrl('program', ['id' => 3_983, 'flavor' => 'pocketcode']);
        break;
      case 2:
        $example_link = $this->generateUrl('program', ['id' => 3_984, 'flavor' => 'pocketcode']);
        break;
      case 3:
        $example_link = $this->generateUrl('program', ['id' => 3_985, 'flavor' => 'pocketcode']);
        break;
      case 4:
        $example_link = $this->generateUrl('program', ['id' => 3_986, 'flavor' => 'pocketcode']);
        break;
      case 5:
        $example_link = $this->generateUrl('program', ['id' => 3_987, 'flavor' => 'pocketcode']);
        break;
      case 6:
        $example_link = $this->generateUrl('program', ['id' => 3_988, 'flavor' => 'pocketcode']);
        break;
      case 7:
        $example_link = $this->generateUrl('program', ['id' => 3_990, 'flavor' => 'pocketcode']);
        break;
      case 8:
        $example_link = $this->generateUrl('program', ['id' => 3_991, 'flavor' => 'pocketcode']);
        break;
      case 9:
        $example_link = $this->generateUrl('program', ['id' => 3_992, 'flavor' => 'pocketcode']);
        break;
      case 10:
        $example_link = $this->generateUrl('program', ['id' => 3_979, 'flavor' => 'pocketcode']);
        break;
      case 11:
        $example_link = $this->generateUrl('program', ['id' => 3_981, 'flavor' => 'pocketcode']);
        break;
      case 12:
        $example_link = $this->generateUrl('program', ['id' => 3_982, 'flavor' => 'pocketcode']);
        break;
    }

    return $example_link;
  }

  private function generateCategoryArray(array $categories): array
  {
    $categories_twig = [];

    /** @var StarterCategory $category */
    foreach ($categories as $category)
    {
      $categories_twig[] = [
        'id' => $category->getId(),
        'alias' => $category->getAlias(),
      ];
    }

    return $categories_twig;
  }

  private function receiveCategoryPrograms(Request $request, array $programs,
                                           ScreenshotRepository $screenshot_repository): array
  {
    $retArray = [
      'CatrobatProjects' => [],
    ];

    /** @var Program $program */
    foreach ($programs as $program)
    {
      $retArray['CatrobatProjects'][] = [
        'ProjectId' => $program->getId(),
        'ProjectName' => $program->getName(),
        'Downloads' => $program->getDownloads(),
        'ScreenshotSmall' => $screenshot_repository->getThumbnailWebPath($program->getId()),
        'ProjectUrl' => ltrim($this->generateUrl('program', [
          'flavor' => $request->get('flavor'),
          'id' => $program->getId(),
        ]), '/'),
      ];
    }

    return $retArray;
  }
}
