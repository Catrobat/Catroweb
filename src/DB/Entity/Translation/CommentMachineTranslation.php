<?php

declare(strict_types=1);

namespace App\DB\Entity\Translation;

use App\DB\Entity\User\Comment\UserComment;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;

#[ORM\Table(name: 'user_comment_machine_translation')]
#[ORM\Entity]
#[HasLifecycleCallbacks]
class CommentMachineTranslation extends MachineTranslation
{
  public function __construct(
    #[ORM\JoinColumn(name: 'comment_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\ManyToOne(targetEntity: UserComment::class)]
    protected UserComment $comment, string $source_language, string $target_language, string $provider, int $usage_count = 1)
  {
    parent::__construct($source_language, $target_language, $provider, $usage_count);
  }

  public function getComment(): UserComment
  {
    return $this->comment;
  }
}
