<?php

declare(strict_types=1);

namespace App\Admin\Moderation;

use App\DB\Entity\Moderation\ContentReport;
use App\DB\Entity\User\User;
use App\DB\Enum\ContentType;
use App\Moderation\AgeSignalDetector;
use App\Moderation\ContentVisibilityManager;
use App\Project\CatrobatFile\ExtractedCatrobatFile;
use App\Project\CatrobatFile\ExtractedFileRepository;
use App\Project\ProjectManager;
use App\Storage\ScreenshotRepository;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\DoctrineORMAdminBundle\Filter\DateTimeRangeFilter;
use Sonata\DoctrineORMAdminBundle\Filter\NumberFilter;
use Sonata\Form\Type\DateTimeRangePickerType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType as SymfonyChoiceType;

/**
 * @phpstan-extends AbstractAdmin<ContentReport>
 */
class ModerationQueueAdmin extends AbstractAdmin
{
  public function __construct(
    private readonly ContentVisibilityManager $visibility_manager,
    private readonly ProjectManager $project_manager,
    private readonly ExtractedFileRepository $extracted_file_repository,
    private readonly ScreenshotRepository $screenshot_repository,
  ) {
  }

  /**
   * What an automatic flag found: flagged_assets, or for reports written before it, parsed from the note.
   *
   * @return list<array{type: 'image'|'text', name: string, score?: float, field?: string}>
   */
  public static function flaggedAssets(ContentReport $report): array
  {
    $assets = $report->getFlaggedAssets();
    if (null !== $assets) {
      return $assets;
    }

    $assets = [];
    $note = (string) $report->getNote();
    if (null !== $report->getReporter() || !str_starts_with($note, 'Automatic: ')) {
      return []; // a reporter's note, whatever it says
    }

    preg_match_all('~image "([^"]+)" has NSFW score ([\d.]+)~', $note, $images, PREG_SET_ORDER);
    foreach ($images as [, $name, $score]) {
      $assets[] = ['type' => 'image', 'name' => $name, 'score' => (float) $score];
    }

    if (1 === preg_match('~^Automatic: adults-only signal "(.+)"~', $note, $signals)) {
      foreach (explode('", "', $signals[1]) as $signal) {
        $assets[] = ['type' => 'text', 'name' => $signal];
      }
    }

    return $assets;
  }

  /**
   * The reported content for the show page: the flagged images and texts first, then a project's other images,
   * sounds and texts (as on Approve Projects).
   *
   * @return array{
   *   flagged: list<array{type: string, name: string, score: float|null, url: string|null, label: string|null, text: string|null}>,
   *   images: list<string>, sounds: list<string>, texts: list<array{label: string, text: string}>
   * }
   */
  public function getReviewAssets(ContentReport $report): array
  {
    $project = ContentType::Project->value === $report->getContentType() ? $this->project_manager->find($report->getContentId()) : null;
    $extracted = null === $project ? null : $this->extracted_file_repository->loadProjectExtractedFile($project);
    $images = array_map(self::url(...), $extracted?->getContainingImagePaths() ?? []);
    $texts = [];
    if (null !== $project) {
      foreach (['Name' => $project->getName(), 'Description' => $project->getDescription(), 'Credits' => $project->getCredits()] as $label => $text) {
        if (null !== $text && '' !== $text) {
          $texts[] = ['label' => $label, 'text' => $text];
        }
      }

      foreach ($extracted?->getContainingStrings() ?? [] as $text) {
        $texts[] = ['label' => 'Code', 'text' => (string) $text];
      }
    }

    $flagged = [];
    $flagged_texts = [];
    foreach (self::flaggedAssets($report) as $asset) {
      if ('' === $asset['name']) {
        continue;
      }

      $score = $asset['score'] ?? null;
      $asset['name'] = 'image' === $asset['type'] ? basename($asset['name']) : $asset['name']; // a file name, never a path

      if ('image' === $asset['type']) {
        $url = null;
        foreach ($images as $i => $image) {
          if (rawurldecode(basename($image)) === $asset['name']) {
            $url = $image;
            unset($images[$i]);
            break;
          }
        }

        if (null === $url && null !== $project) {
          $url = $this->screenshotUrl($report->getContentId(), $asset['name'], $extracted);
        }

        $flagged[] = ['type' => 'image', 'name' => $asset['name'], 'score' => $score, 'url' => $url, 'label' => null, 'text' => null];
        continue;
      }

      $matched = false;
      $label = ucfirst($asset['field'] ?? ''); // name, description, credits: only the text it was found in
      foreach ($texts as $i => $text) {
        if ('' !== $label && $label !== $text['label']) {
          continue;
        }

        // The detector matched the text without its invisible characters ("n\u{200B}sfw" is "nsfw").
        if (false !== mb_stripos(AgeSignalDetector::removeInvisibleCharacters($text['text']), $asset['name'])) {
          $flagged[] = ['type' => 'text', 'name' => $asset['name'], 'score' => $score, 'url' => null, 'label' => $text['label'], 'text' => $text['text']];
          $flagged_texts[$i] = true;
          $matched = true;
        }
      }

      if (!$matched) {
        $flagged[] = ['type' => 'text', 'name' => $asset['name'], 'score' => $score, 'url' => null, 'label' => null, 'text' => null];
      }
    }

    return [
      'flagged' => $flagged,
      'images' => array_values($images),
      'sounds' => array_values(array_map(self::url(...), $extracted?->getContainingSoundPaths() ?? [])),
      'texts' => array_values(array_diff_key($texts, $flagged_texts)),
    ];
  }

  /**
   * A flagged screenshot: the site's (screen_<id>.png) or the project's own (next to code.xml, or in a scene).
   * Null when it no longer exists, so a placeholder is not shown as the flagged image.
   */
  private function screenshotUrl(string $project_id, string $name, ?ExtractedCatrobatFile $extracted): ?string
  {
    if (str_starts_with($name, 'screen_')) {
      $path = $this->screenshot_repository->getScreenshotWebPath($project_id);

      return ScreenshotRepository::DEFAULT_SCREENSHOT === $path ? null : '/'.ltrim($path, '/');
    }

    if (null === $extracted) {
      return null;
    }

    // ponytail: the first scene with a screenshot of that name; the scanner stores no scene.
    foreach (['', ...array_map(static fn (string $dir): string => basename($dir).'/', glob($extracted->getPath().'*', GLOB_ONLYDIR) ?: [])] as $dir) {
      if (is_file($extracted->getPath().$dir.$name)) {
        return self::url('/'.$extracted->getWebPath().$dir.$name);
      }
    }

    return null;
  }

  private static function url(string $path): string
  {
    return implode('/', array_map(rawurlencode(...), explode('/', $path)));
  }

  public function getContentOwnerId(ContentReport $report): ?string
  {
    $content_type = ContentType::tryFrom($report->getContentType());
    if (null === $content_type) {
      return null;
    }

    return $this->visibility_manager->getContentOwnerId($content_type, $report->getContentId());
  }

  #[\Override]
  protected function generateBaseRouteName(bool $isChildAdmin = false): string
  {
    return 'admin_moderation_reports';
  }

  #[\Override]
  protected function generateBaseRoutePattern(bool $isChildAdmin = false): string
  {
    return 'moderation/report';
  }

  #[\Override]
  protected function configureDefaultSortValues(array &$sortValues): void
  {
    $sortValues[DatagridInterface::SORT_BY] = 'created_at';
    $sortValues[DatagridInterface::SORT_ORDER] = 'DESC';
  }

  #[\Override]
  protected function configureDatagridFilters(DatagridMapper $filter): void
  {
    $filter
      ->add('content_type', null, ['label' => 'Content Type'])
      ->add('state', NumberFilter::class, [
        'field_type' => SymfonyChoiceType::class,
        'field_options' => ['choices' => ['New' => 1, 'Accepted' => 2, 'Rejected' => 3]],
      ])
      ->add('category')
      ->add('created_at', DateTimeRangeFilter::class, [
        'field_type' => DateTimeRangePickerType::class,
      ])
      ->add('reporter', null, ['label' => 'Reporter'])
    ;
  }

  #[\Override]
  protected function configureListFields(ListMapper $list): void
  {
    $list
      ->add('id')
      ->add('created_at', null, ['label' => 'Date'])
      ->add('content_type', null, ['label' => 'Type'])
      ->add('content_id', null, [
        'label' => 'Content',
        'template' => 'Admin/CRUD/list__field_content_link.html.twig',
      ])
      ->add('category')
      ->add('note', null, ['sortable' => false])
      ->add('reporter', EntityType::class, ['class' => User::class, 'label' => 'Reporter'])
      ->add('content_owner', null, [
        'label' => 'Content Owner',
        'sortable' => false,
        'virtual_field' => true,
        'template' => 'Admin/CRUD/list__field_content_owner.html.twig',
      ])
      ->add('reporter_trust_score', null, ['label' => 'Trust Score'])
      ->add('state', 'choice', [
        'choices' => [1 => 'New', 2 => 'Accepted', 3 => 'Rejected'],
      ])
      ->add(ListMapper::NAME_ACTIONS, null, ['actions' => [
        'acceptReport' => ['template' => 'Admin/CRUD/list__action_accept_report.html.twig'],
        'rejectReport' => ['template' => 'Admin/CRUD/list__action_reject_report.html.twig'],
        'warnReport' => ['template' => 'Admin/CRUD/list__action_warn_report.html.twig'],
        'show' => [],
      ]])
    ;
  }

  #[\Override]
  protected function configureShowFields(ShowMapper $show): void
  {
    $show
      ->add('created_at', null, ['label' => 'Date'])
      ->add('content_type', null, ['label' => 'Type'])
      ->add('content_id', null, ['label' => 'Content', 'template' => 'Admin/Moderation/ShowContentLink.html.twig'])
      ->add('category')
      ->add('note')
      ->add('reporter', null, ['label' => 'Reporter'])
      ->add('state', 'choice', ['choices' => [1 => 'New', 2 => 'Accepted', 3 => 'Rejected']])
      ->add('actions', null, [
        'label' => 'Actions',
        'accessor' => static fn (): null => null, // just the buttons of the list
        'template' => 'Admin/Moderation/ReportActions.html.twig',
      ])
      ->add('assets', null, [
        'label' => 'Assets',
        'accessor' => $this->getReviewAssets(...),
        'template' => 'Admin/Moderation/ReportAssets.html.twig',
      ])
    ;
  }

  #[\Override]
  protected function configureRoutes(RouteCollectionInterface $collection): void
  {
    $collection->add('acceptReport', $this->getRouterIdParameter().'/acceptReport');
    $collection->add('rejectReport', $this->getRouterIdParameter().'/rejectReport');
    $collection->add('hideWarnReport', $this->getRouterIdParameter().'/hideWarnReport', methods: ['POST']);
    $collection->add('warnReport', $this->getRouterIdParameter().'/warnReport', methods: ['POST']);
    $collection->remove('create')->remove('delete')->remove('export');
  }
}
