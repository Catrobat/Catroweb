<?php

namespace App\Entity\Translation;

use App\Entity\UserComment;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

/**
 * @ORM\Entity
 * @ORM\Table(name="user_comment_machine_translation")
 * @HasLifecycleCallbacks
 */
class CommentMachineTranslation extends MachineTranslation
{
  /**
   * @ORM\ManyToOne(targetEntity="App\Entity\UserComment")
   * @ORM\JoinColumn(name="comment_id", referencedColumnName="id", onDelete="CASCADE")
   */
  protected UserComment $comment;

  public function __construct(UserComment $comment, string $source_language, string $target_language, string $provider, int $usage_count = 1)
  {
    parent::__construct($source_language, $target_language, $provider, $usage_count);
    $this->comment = $comment;
  }

  public function getComment(): UserComment
  {
    return $this->comment;
  }
}
