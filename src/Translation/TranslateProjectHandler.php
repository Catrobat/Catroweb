<?php

declare(strict_types=1);

namespace App\Translation;

use App\Project\ProjectManager;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler]
final readonly class TranslateProjectHandler
{
  public function __construct(private ProjectManager $project_manager, private TranslationDelegate $translation_delegate)
  {
  }

  public function __invoke(TranslateProject $message): void
  {
    $project = $this->project_manager->find($message->project_id);
    if (null === $project) {
      return; // deleted meanwhile
    }

    // Never throws: a failure is reported to the waiting browser, a Messenger retry would come too late for it.
    $this->translation_delegate->translateProject($project, $message->source_language, $message->target_language);
  }
}
