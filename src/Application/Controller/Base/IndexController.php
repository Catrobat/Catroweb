<?php

namespace App\Application\Controller\Base;

use App\DB\Entity\Project\Special\FeaturedProgram;
use App\DB\EntityRepository\Project\Special\FeaturedRepository;
use App\Storage\ImageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class IndexController extends AbstractController
{
  public function __construct(protected ImageRepository $image_repository, protected FeaturedRepository $featured_repository)
  {
  }

  #[Route(path: '/', name: 'index', methods: ['GET'])]
  public function indexAction(Request $request): Response
  {
    $flavor = $request->attributes->get('flavor');

    return $this->render('Index/index.html.twig', [
      'featured' => $this->getFeaturedSliderData($flavor),
    ]);
  }

  protected function getFeaturedSliderData(string $flavor): array
  {
    if ('phirocode' === $flavor) {
      $featured_items = $this->featured_repository->getFeaturedItems('pocketcode', 10, 0);
    } else {
      $featured_items = $this->featured_repository->getFeaturedItems($flavor, 10, 0);
    }

    $featuredData = [];
    /** @var FeaturedProgram $item */
    foreach ($featured_items as $item) {
      $info = [];
      if (null !== $item->getProgram()) {
        if ($flavor) {
          $info['url'] = $this->generateUrl('program',
            ['id' => $item->getProgram()->getId(), 'theme' => $flavor]);
        } else {
          $info['url'] = $this->generateUrl('program', ['id' => $item->getProgram()->getId()]);
        }
      } else {
        $info['url'] = $item->getUrl();
      }
      $info['image'] = $this->image_repository->getWebPath($item->getId(), $item->getImageType(), true);

      $featuredData[] = $info;
    }

    return $featuredData;
  }
}
