<?php

namespace Catrobat\AppBundle\Listeners\View;

use Catrobat\AppBundle\Entity\Program;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use Catrobat\AppBundle\Responses\ProgramListResponse;
use Catrobat\AppBundle\Services\ScreenshotRepository;
use Catrobat\AppBundle\Services\Formatter\ElapsedTimeStringFormatter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Router;

/**
 * Class ProgramListSerializer
 * @package Catrobat\AppBundle\Listeners\View
 */
class ProgramListSerializer
{
  /**
   * @var RequestStack
   */
  private $request_stack;
  /**
   * @var Router
   */
  private $router;
  /**
   * @var ScreenshotRepository
   */
  private $screenshot_repository;
  /**
   * @var ElapsedTimeStringFormatter
   */
  private $time_formatter;

  /**
   * ProgramListSerializer constructor.
   *
   * @param RequestStack               $request_stack
   * @param Router                     $router
   * @param ScreenshotRepository       $screenshot_repository
   * @param ElapsedTimeStringFormatter $time_formatter
   */
  public function __construct(RequestStack $request_stack, Router $router, ScreenshotRepository $screenshot_repository, ElapsedTimeStringFormatter $time_formatter)
  {
    $this->request_stack = $request_stack;
    $this->router = $router;
    $this->screenshot_repository = $screenshot_repository;
    $this->time_formatter = $time_formatter;
  }

  /**
   * @param GetResponseForControllerResultEvent $event
   */
  public function onKernelView(GetResponseForControllerResultEvent $event)
  {
    /**
     * @var $program Program
     */

    $result = $event->getControllerResult();
    if (!($result instanceof ProgramListResponse))
    {
      return;
    }

    $programs = $result->getPrograms();
    $details = $result->getShowDetails();
    $request = $this->request_stack->getCurrentRequest();

    $retArray = [];
    $retArray['CatrobatProjects'] = [];
    if ($programs != null)
    {
      foreach ($programs as $program)
      {
        $new_program = [];
        $new_program['ProjectId'] = $program->getId();
        $new_program['ProjectName'] = $program->getName();
        if ($details === true)
        {
          $new_program['ProjectNameShort'] = $program->getName();
          $new_program['Author'] = $program->getUser()->getUserName();
          $new_program['Description'] = $program->getDescription();
          $new_program['Version'] = $program->getCatrobatVersionName();
          $new_program['Views'] = $program->getViews();
          $new_program['Downloads'] = $program->getDownloads();
          $new_program['Private'] = $program->getPrivate();
          $new_program['Uploaded'] = $program->getUploadedAt()->getTimestamp();
          $new_program['UploadedString'] = $this->time_formatter->getElapsedTime($program->getUploadedAt()
            ->getTimestamp());
          $new_program['ScreenshotBig'] = $this->screenshot_repository->getScreenshotWebPath($program->getId());
          $new_program['ScreenshotSmall'] = $this->screenshot_repository->getThumbnailWebPath($program->getId());
          $new_program['ProjectUrl'] = ltrim($this->generateUrl('program', [
            'flavor' => $request->attributes->get('flavor'),
            'id'     => $program->getId(),
          ]), '/');
          $new_program['DownloadUrl'] = ltrim($this->generateUrl('download', [
            'id' => $program->getId(),
          ]), '/');
          $new_program['FileSize'] = $program->getFilesize() / 1048576;
        }
        $retArray['CatrobatProjects'][] = $new_program;
      }
    }
    $retArray['completeTerm'] = '';
    $retArray['preHeaderMessages'] = '';

    if ($result->isIsUserSpecificRecommendation())
    {
      $retArray['isUserSpecificRecommendation'] = true;
    }

    $retArray['CatrobatInformation'] = [
      'BaseUrl'           => ($request->isSecure() ? 'https://' : 'http://') . $request->getHttpHost() . '/',
      'TotalProjects'     => $result->getTotalPrograms(),
      'ProjectsExtension' => '.catrobat',
    ];

    $event->setResponse(JsonResponse::create($retArray));
  }

  /**
   * @param       $route
   * @param array $parameters
   *
   * @return string
   */
  public function generateUrl($route, $parameters = [])
  {
    return $this->router->generate($route, $parameters);
  }
}