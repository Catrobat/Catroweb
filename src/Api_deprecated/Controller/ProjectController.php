<?php

namespace App\Api_deprecated\Controller;

use App\Api_deprecated\Responses\ProjectListResponse;
use App\Application\Twig\TwigExtension;
use App\DB\Entity\Project\ProjectLike;
use App\Project\ProjectManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @deprecated
 */
class ProjectController extends AbstractController
{
  /**
   * @deprecated
   */
  #[Route(path: '/api/projects/getInfoById.json', name: 'api_info_by_id', defaults: ['_format' => 'json'], methods: ['GET'])]
  public function showProjectAction(Request $request, ProjectManager $project_manager): JsonResponse|ProjectListResponse
  {
    $id = (string) $request->query->get('id', '0');
    $projects = [];
    $project = $project_manager->find($id);
    if (null === $project) {
      return new JsonResponse(['Error' => 'Project not found (uploaded)', 'preHeaderMessages' => '']);
    }
    $numbOfTotalProjects = 1;
    $projects[] = $project;

    return new ProjectListResponse($projects, $numbOfTotalProjects);
  }

  /**
   * @deprecated
   *
   * @throws \Exception
   */
  #[Route(path: '/api/project/{id}/likes', name: 'api_project_likes', methods: ['GET'])]
  public function projectLikesAction(string $id, ProjectManager $project_manager): JsonResponse
  {
    $project = $project_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      throw $this->createNotFoundException("Can't like a project that's not visible to you!; Id: ``{$id}");
    }
    $data = [];
    $user_objects = [];
    /** @var ProjectLike $like */
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

  /**
   * @deprecated
   *
   * @throws NotFoundHttpException
   */
  #[Route(path: '/api/project/{id}/likes/count', name: 'api_project_likes_count', methods: ['GET'])]
  public function projectLikesCountAction(Request $request, string $id, ProjectManager $project_manager, TranslatorInterface $translator): JsonResponse
  {
    $project = $project_manager->findProjectIfVisibleToCurrentUser($id);
    if (null === $project) {
      throw $this->createNotFoundException("Can't count likes of a project that's not visible to you!; Id: `{$id}`");
    }
    $user_locale = $request->getLocale();
    $data = new \stdClass();
    $data->total = new \stdClass();
    $data->total->value = $project_manager->totalLikeCount($id);
    $data->total->stringValue = TwigExtension::humanFriendlyNumber(
      $data->total->value, $translator, $user_locale
    );
    foreach (ProjectLike::$VALID_TYPES as $type_id) {
      $type_name = ProjectLike::$TYPE_NAMES[$type_id];
      $data->{$type_name} = new \stdClass();
      $data->{$type_name}->value = $project_manager->likeTypeCount($id, $type_id);
      $data->{$type_name}->stringValue = TwigExtension::humanFriendlyNumber(
        $data->{$type_name}->value, $translator, $user_locale
      );
    }

    return new JsonResponse($data);
  }
}
