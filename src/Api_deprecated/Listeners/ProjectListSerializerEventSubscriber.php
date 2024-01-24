<?php

namespace App\Api_deprecated\Listeners;

use App\Api_deprecated\Responses\ProjectListResponse;
use App\DB\Entity\Project\Program;
use App\Storage\ImageRepository;
use App\Storage\ScreenshotRepository;
use App\Utils\ElapsedTimeStringFormatter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;

/**
 * @deprecated
 */
class ProjectListSerializerEventSubscriber implements EventSubscriberInterface
{
  public function __construct(private readonly ScreenshotRepository $screenshot_repository, private readonly RequestStack $request_stack, private readonly RouterInterface $router, private readonly ElapsedTimeStringFormatter $time_formatter, private readonly ImageRepository $example_image_repository, private readonly ParameterBagInterface $parameter_bag)
  {
  }

  public function onKernelView(ViewEvent $event): void
  {
    $result = $event->getControllerResult();
    if (!($result instanceof ProjectListResponse)) {
      return;
    }

    $projects = $result->getProjects();
    $request = $this->request_stack->getCurrentRequest();

    $retArray = [];
    $retArray['CatrobatProjects'] = [];

    /** @var Program $project */
    foreach ($projects as $project) {
      $new_project = [];
      $example = false;
      if ($project->isExample()) {
        $new_project['ExampleId'] = $project->getId();
        $new_project['Extension'] = $project->getImageType();
        $example = true;
        $project = $project->getProgram();
      }
      $new_project['ProjectId'] = $project->getId();
      $new_project['ProjectName'] = $project->getName();
      $new_project['ProjectNameShort'] = $project->getName();
      $new_project['Author'] = $project->getUser()->getUserIdentifier();
      $new_project['Description'] = $project->getDescription();
      $new_project['Version'] = $project->getCatrobatVersionName();
      $new_project['Views'] = $project->getViews();
      $new_project['Downloads'] = $project->getDownloads();
      $new_project['Private'] = $project->getPrivate();
      $new_project['Uploaded'] = $project->getUploadedAt()->getTimestamp();
      $new_project['UploadedString'] = $this->time_formatter->format($project->getUploadedAt()
        ->getTimestamp());
      if ($example) {
        $new_project['ScreenshotBig'] = $this->example_image_repository->getWebPath(intval($new_project['ExampleId']), $new_project['Extension'], false);
        $new_project['ScreenshotSmall'] = $this->example_image_repository->getWebPath(intval($new_project['ExampleId']), $new_project['Extension'], false);
      } else {
        $new_project['ScreenshotBig'] = $this->screenshot_repository->getScreenshotWebPath($project->getId());
        $new_project['ScreenshotSmall'] = $this->screenshot_repository->getThumbnailWebPath($project->getId());
      }
      $new_project['ProjectUrl'] = ltrim($this->generateUrl('program', [
        'theme' => $this->parameter_bag->get('umbrellaTheme'),
        'id' => $project->getId(),
      ]), '/');
      $new_project['DownloadUrl'] = ltrim($this->generateUrl('open_api_server_projects_projectidcatrobatget', [
        'id' => $project->getId(),
      ]), '/');
      $new_project['FileSize'] = $project->getFilesize() / 1_048_576;
      $retArray['CatrobatProjects'][] = $new_project;
    }

    $retArray['completeTerm'] = '';
    $retArray['preHeaderMessages'] = '';

    $retArray['CatrobatInformation'] = [
      'BaseUrl' => $request->getSchemeAndHttpHost().'/',
      'TotalProjects' => $result->getTotalProjects(),
      'ProjectsExtension' => '.catrobat',
    ];

    $event->setResponse(new JsonResponse($retArray));
  }

  public function generateUrl(string $route, array $parameters = []): string
  {
    return $this->router->generate($route, $parameters);
  }

  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::VIEW => 'onKernelView'];
  }
}
