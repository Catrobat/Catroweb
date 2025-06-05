<?php

declare(strict_types=1);

namespace App\Api_deprecated\Controller;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Project\ProgramLike;
use App\Project\ProjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

/**
 * @deprecated
 */
class ProjectController extends AbstractController
{
  /**
   * @deprecated
   *
   * @throws \Exception
   */
  #[Route(path: '/api/project/{id}/likes', name: 'api_project_likes', methods: ['GET'])]
  public function projectLikes(string $id, ProjectManager $project_manager): JsonResponse
  {
    $project = $project_manager->findProjectIfVisibleToCurrentUser($id);
    if (!$project instanceof Program) {
      throw $this->createNotFoundException('Can\'t like a project that\'s not visible to you!; Id: ``'.$id);
    }

    $data = [];
    $user_objects = [];
    /** @var ProgramLike $like */
    foreach ($project->getLikes()->getIterator() as $like) {
      if (array_key_exists($like->getUser()->getId(), $user_objects)) {
        $obj = $user_objects[$like->getUser()->getId()];
        $obj->types[] = $like->getTypeAsString();
      } else {
        $obj = new \stdClass();
        $obj->user = new \stdClass();
        $obj->user->id = $like->getUser()->getId();
        $obj->user->name = $like->getUser()->getUsername();
        $obj->types = [$like->getTypeAsString()];
        $data[] = $obj;
        $user_objects[$like->getUser()->getId()] = $obj;
      }
    }

    return new JsonResponse($data);
  }
}
