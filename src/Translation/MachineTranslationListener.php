<?php

namespace App\Translation;

use App\Entity\Program;
use App\Entity\ProgramManager;
use App\Entity\Translation\CommentMachineTranslation;
use App\Entity\Translation\ProjectMachineTranslation;
use App\Entity\UserComment;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpKernel\Event\TerminateEvent;

class MachineTranslationListener
{
  private EntityManagerInterface $entity_manager;
  private ProgramManager $program_manager;

  public function __construct(EntityManagerInterface $entity_manager, ProgramManager $program_manager)
  {
    $this->entity_manager = $entity_manager;
    $this->program_manager = $program_manager;
  }

  public function onTerminateEvent(TerminateEvent $event): void
  {
    if (200 !== $event->getResponse()->getStatusCode()) {
      return;
    }

    $request = $event->getRequest();
    $path = $request->getPathInfo();

    if (str_contains($path, '/translate/comment/')) {
      $this->persistCommentTranslation($event);
    } elseif (str_contains($path, '/translate/project/')) {
      $this->persistProgramTranslation($event);
    }
  }

  private function persistCommentTranslation(TerminateEvent $event): void
  {
    $json_response = $event->getResponse()->getContent();
    $array_response = json_decode($json_response, true);
    $comment_id = $array_response['id'];
    $source_language = $array_response['source_language'];
    $target_language = $array_response['target_language'];
    $provider = $array_response['provider'];

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

  private function persistProgramTranslation(TerminateEvent $event): void
  {
    $json_response = $event->getResponse()->getContent();
    $array_response = json_decode($json_response, true);
    $project_id = $array_response['id'];
    $source_language = $array_response['source_language'];
    $target_language = $array_response['target_language'];
    $provider = $array_response['provider'];

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
