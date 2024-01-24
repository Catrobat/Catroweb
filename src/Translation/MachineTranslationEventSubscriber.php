<?php

namespace App\Translation;

use App\DB\Entity\Project\Program;
use App\DB\Entity\Translation\CommentMachineTranslation;
use App\DB\Entity\Translation\ProjectMachineTranslation;
use App\DB\Entity\User\Comment\UserComment;
use App\Project\ProjectManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\TerminateEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class MachineTranslationEventSubscriber implements EventSubscriberInterface
{
  private const CACHED_PROVIDER = 'etag';
  private readonly int $project_caching_threshold;

  public function __construct(private readonly EntityManagerInterface $entity_manager, private readonly ProjectManager $project_manager, ParameterBagInterface $parameters)
  {
    $pct = $parameters->get('catrobat.translations.project_cache_threshold');
    $this->project_caching_threshold = is_numeric($pct) ? (int) $pct : 0;
  }

  public function onTerminateEvent(TerminateEvent $event): void
  {
    $status_code = $event->getResponse()->getStatusCode();
    if (200 !== $status_code && 304 !== $status_code) {
      return;
    }

    $request = $event->getRequest();
    $path = $request->getPathInfo();

    if (str_contains($path, '/translate/comment/')) {
      if (200 === $status_code) {
        $this->persistCommentTranslation($event);
      } else {
        $this->persistCachedCommentTranslation($request);
      }
    } elseif (str_contains($path, '/translate/project/')) {
      if (200 === $status_code) {
        $this->persistProjectTranslation($event);
      } else {
        $this->persistCachedProjectTranslation($request);
      }
    }
  }

  private function persistCommentTranslation(TerminateEvent $event): void
  {
    [$comment_id, $source_language, $target_language, $provider] = $this->getParameters($event);

    $this->findCommentAndIncrement($comment_id, $source_language, $target_language, $provider);
  }

  private function persistProjectTranslation(TerminateEvent $event): void
  {
    [$project_id, $source_language, $target_language, $provider, $cache] = $this->getParameters($event);
    [$translated_name, $translated_description, $translated_credits] = $this->getTranslation($event);

    $this->findProjectAndIncrement($project_id, $source_language, $target_language, $provider, $cache,
      $translated_name, $translated_description, $translated_credits);
  }

  private function persistCachedCommentTranslation(Request $request): void
  {
    $comment_id = intval($this->getId($request->getPathInfo()));
    [$source_language, $target_language] = $this->getLanguages($request);

    $this->findCommentAndIncrement($comment_id, $source_language, $target_language, self::CACHED_PROVIDER);
  }

  private function persistCachedProjectTranslation(Request $request): void
  {
    $project_id = $this->getId($request->getPathInfo());
    [$source_language, $target_language] = $this->getLanguages($request);

    $this->findProjectAndIncrement($project_id, $source_language, $target_language, self::CACHED_PROVIDER);
  }

  private function getParameters(TerminateEvent $event): array
  {
    $json = $this->getJson($event);

    return [
      $json['id'],
      $json['source_language'],
      $json['target_language'],
      $json['provider'],
      $json['_cache'],
    ];
  }

  private function getId(string $path): string
  {
    return substr((string) strrchr($path, '/'), 1);
  }

  private function getLanguages(Request $request): array
  {
    return [
      $request->query->get('source_language', ''),
      $request->query->get('target_language'),
    ];
  }

  private function getTranslation(TerminateEvent $event): array
  {
    $json = $this->getJson($event);

    return [
      $json['translated_title'],
      $json['translated_description'],
      $json['translated_credit'],
    ];
  }

  private function getJson(TerminateEvent $event): array
  {
    $json_response = $event->getResponse()->getContent();

    return json_decode($json_response, true, 512, JSON_THROW_ON_ERROR);
  }

  private function findCommentAndIncrement(int $comment_id, string $source_language, string $target_language, string $provider): void
  {
    /** @var UserComment|null $comment */
    $comment = $this->entity_manager->getRepository(UserComment::class)->find($comment_id);

    if (null === $comment) {
      return;
    }

    /** @var CommentMachineTranslation|null $comment_machine_translation */
    $comment_machine_translation = $this->entity_manager->getRepository(CommentMachineTranslation::class)
      ->findOneBy([
        'comment' => $comment,
        'source_language' => $source_language,
        'target_language' => $target_language,
        'provider' => $provider,
      ])
    ;

    if (null === $comment_machine_translation) {
      $comment_machine_translation = new CommentMachineTranslation($comment, $source_language, $target_language, $provider);
    } else {
      $comment_machine_translation->incrementCount();
    }

    $this->entity_manager->persist($comment_machine_translation);
    $this->entity_manager->flush();
  }

  private function findProjectAndIncrement(string $project_id, string $source_language, string $target_language,
    string $provider, string $cache = null,
    string $translated_name = null, string $translated_description = null, string $translated_credits = null): void
  {
    /** @var Program|null $project */
    $project = $this->project_manager->find($project_id);

    if (null === $project) {
      return;
    }

    $provider = $cache ?? $provider;

    /** @var ProjectMachineTranslation|null $project_machine_translation */
    $project_machine_translation = $this->entity_manager->getRepository(ProjectMachineTranslation::class)
      ->findOneBy([
        'project' => $project,
        'source_language' => $source_language,
        'target_language' => $target_language,
        'provider' => $provider,
      ])
    ;

    if (null === $project_machine_translation) {
      $project_machine_translation = new ProjectMachineTranslation($project, $source_language, $target_language, $provider);
    } else {
      $project_machine_translation->incrementCount();
    }

    if ($this->cacheable($cache) && $project_machine_translation->getUsagePerMonth() >= $this->project_caching_threshold) {
      $project_machine_translation->setCachedTranslation($translated_name, $translated_description, $translated_credits);
    }

    $this->entity_manager->persist($project_machine_translation);
    $this->entity_manager->flush();
  }

  private function cacheable(?string $cache): bool
  {
    // do not cache "cached" value, i.e. double cache
    return null === $cache;
  }

  public static function getSubscribedEvents(): array
  {
    return [KernelEvents::TERMINATE => 'onTerminateEvent'];
  }
}
