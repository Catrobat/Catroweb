<?php

namespace App\Catrobat\Listeners\View;

use App\Entity\Template;
use App\Entity\TemplateManager;
use App\Catrobat\Responses\TemplateListResponse;
use Symfony\Component\HttpKernel\Event\GetResponseForControllerResultEvent;
use App\Catrobat\Services\ScreenshotRepository;
use App\Catrobat\Services\Formatter\ElapsedTimeStringFormatter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Class TemplateListSerializer
 * @package App\Catrobat\Listeners\View
 */
class TemplateListSerializer
{
  /**
   * @var RequestStack
   */
  private $request_stack;
  /**
   * @var
   */
  private $template_path;
  /**
   * @var ScreenshotRepository
   */
  private $screenshot_repository;
  /**
   * @var ElapsedTimeStringFormatter
   */
  private $time_formatter;

  /**
   * TemplateListSerializer constructor.
   *
   * @param $template_screenshot_repository
   * @param RequestStack $request_stack
   * @param $catrobat_template_storage_path
   * @param ElapsedTimeStringFormatter $time_formatter
   */
  public function __construct($template_screenshot_repository, RequestStack $request_stack,
                              $catrobat_template_storage_path, ElapsedTimeStringFormatter $time_formatter)
  {
    $this->request_stack = $request_stack;
    $this->template_path = $catrobat_template_storage_path;
    $this->screenshot_repository = $template_screenshot_repository;
    $this->time_formatter = $time_formatter;
  }

  /**
   * @param GetResponseForControllerResultEvent $event
   */
  public function onKernelView(GetResponseForControllerResultEvent $event)
  {
    $result = $event->getControllerResult();
    if (!($result instanceof TemplateListResponse))
    {
      return;
    }

    $templates = $result->getTemplates();
    $request = $this->request_stack->getCurrentRequest();

    $retArray = [];
    $retArray['CatrobatTemplates'] = [];
    if ($templates != null)
    {
      foreach ($templates as $template)
      {
        $new_template = $this->generateTemplateArray($template);
        if ($new_template != null)
        {
          $retArray['CatrobatTemplates'][] = $new_template;
        }
      }
    }
    $retArray['BaseUrl'] = ($request->isSecure() ? 'https://' : 'http://') . $request->getHttpHost() . '/';
    $retArray['ProjectsExtension'] = '.catrobat';

    $event->setResponse(JsonResponse::create($retArray));
  }

  /**
   * @param      $id
   * @param bool $landscape
   *
   * @return string
   */
  public function generateUrl($id, $landscape = true)
  {
    $prefix = TemplateManager::PORTRAIT_PREFIX;
    if ($landscape)
    {
      $prefix = TemplateManager::LANDSCAPE_PREFIX;
    }

    return ltrim($this->template_path . $prefix . $id . '.catrobat', '/');
  }

  /**
   * @param $template Template
   *
   * @return array
   */
  public function generateTemplateArray($template)
  {
    $landscape = $this->generateUrl($template->getId());
    $portrait = $this->generateUrl($template->getId(), false);

    if (!file_exists($landscape) && !file_exists($portrait))
    {
      return null;
    }

    $new_template = [];
    $new_template['id'] = $template->getId();
    $new_template['name'] = $template->getName();
    $new_template['thumbnail'] = $this->screenshot_repository->getThumbnailWebPath($template->getId());

    if (file_exists($landscape))
    {
      $new_template['landscape'] = $landscape;
    }

    if (file_exists($portrait))
    {
      $new_template['portrait'] = $portrait;
    }

    return $new_template;
  }
}