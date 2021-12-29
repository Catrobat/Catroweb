<?php

namespace App\Translation;

use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\Translation\CommentMachineTranslation;
use App\Entity\Translation\ProjectMachineTranslation;
use App\Entity\UserComment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class MachineTranslationListener
{
  private const CACHED_PROVIDER = 'etag';

  private EntityManagerInterface $entity_manager;
  private ProgramManager $program_manager;

  public function __construct(EntityManagerInterface $entity_manager, ProgramManager $program_manager)
  {
    $this->entity_manager = $entity_manager;
    $this->program_manager = $program_manager;
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
        $this->persistProgramTranslation($event);
      } else {
        $this->persistCachedProgramTranslation($request);
      }
    }
  }

  private function persistCommentTranslation(TerminateEvent $event): void
  {
    [$comment_id, $source_language, $target_language, $provider] = $this->getParameters($event);

    $this->findCommentAndIncrement($comment_id, $source_language, $target_language, $provider);
  }

  private function persistProgramTranslation(TerminateEvent $event): void
  {
    [$project_id, $source_language, $target_language, $provider] = $this->getParameters($event);

    $this->findProjectAndIncrement($project_id, $source_language, $target_language, $provider);
  }

  private function persistCachedCommentTranslation(Request $request): void
  {
    $comment_id = intval($this->getId($request->getPathInfo()));
    [$source_language, $target_language] = $this->getLanguages($request);

    $this->findCommentAndIncrement($comment_id, $source_language, $target_language, self::CACHED_PROVIDER);
  }

  private function persistCachedProgramTranslation(Request $request): void
  {
    $project_id = $this->getId($request->getPathInfo());
    [$source_language, $target_language] = $this->getLanguages($request);

    $this->findProjectAndIncrement($project_id, $source_language, $target_language, self::CACHED_PROVIDER);
  }

  private function getParameters(TerminateEvent $event): array
  {
    $json_response = $event->getResponse()->getContent();
    $array_response = json_decode($json_response, true);

    return [
      $array_response['id'],
      $array_response['source_language'],
      $array_response['target_language'],
      $array_response['provider'],
    ];
  }

  private function getId(string $path): string
  {
    return substr(strrchr($path, '/'), 1);
  }

  private function getLanguages(Request $request): array
  {
    return [
      $request->query->get('source_language', ''),
      $request->query->get('target_language'),
    ];
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

  private function findProjectAndIncrement(string $project_id, string $source_language, string $target_language, string $provider): void
  {
    /** @var Program|null $project */
    $project = $this->program_manager->find($project_id);

    if (null === $project) {
      return;
    }

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

    $this->entity_manager->persist($project_machine_translation);
    $this->entity_manager->flush();
  }
}
