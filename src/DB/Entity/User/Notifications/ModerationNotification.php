<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Notifications;

use App\DB\Entity\User\User;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
class ModerationNotification extends CatroNotification
{
  private string $twig_template = 'User/Notification/Type/Moderation.html.twig';

  /**
   * Why the owner's content was hidden or they were warned, translated when the notification is shown: the report
   * category, the names of flagged images (or that the screenshot was), the flagged text fields.
   *
   * @var array{category?: string, screenshot?: bool, cover?: bool, images?: list<string>, fields?: list<string>}|null
   */
  #[ORM\Column(name: 'moderation_reason', type: Types::JSON, nullable: true)]
  private ?array $moderation_reason = null;

  public function __construct(
    User $user,
    #[ORM\Column(name: 'moderation_content_type', type: Types::STRING, length: 20, nullable: true)]
    private ?string $content_type = null,
    #[ORM\Column(name: 'moderation_content_id', type: Types::STRING, length: 255, nullable: true)]
    private ?string $content_id = null,
    #[ORM\Column(name: 'moderation_action', type: Types::STRING, length: 30, nullable: true)]
    private ?string $moderation_action = null,
    string $message = '',
  ) {
    parent::__construct($user, '', $message, 'moderation');
  }

  public function getContentType(): ?string
  {
    return $this->content_type;
  }

  public function setContentType(?string $content_type): void
  {
    $this->content_type = $content_type;
  }

  public function getContentId(): ?string
  {
    return $this->content_id;
  }

  public function setContentId(?string $content_id): void
  {
    $this->content_id = $content_id;
  }

  public function getModerationAction(): ?string
  {
    return $this->moderation_action;
  }

  public function setModerationAction(?string $moderation_action): void
  {
    $this->moderation_action = $moderation_action;
  }

  /**
   * @return array{category?: string, screenshot?: bool, cover?: bool, images?: list<string>, fields?: list<string>}|null
   */
  public function getModerationReason(): ?array
  {
    return $this->moderation_reason;
  }

  /**
   * @param array{category?: string, screenshot?: bool, cover?: bool, images?: list<string>, fields?: list<string>}|null $moderation_reason
   */
  public function setModerationReason(?array $moderation_reason): self
  {
    $this->moderation_reason = $moderation_reason;

    return $this;
  }

  /**
   * The reason for a report category and what an automatic flag found (ContentReport::getFlaggedAssets()).
   * Image names lose the hash prefix and extension Catrobat gives look files: "<md5>_Mom.png" is "Mom". A studio's
   * only image is its cover.
   *
   * @param list<array{type: 'image'|'text', name: string, score?: float, field?: string}>|null $flagged_assets
   *
   * @return array{category?: string, screenshot?: bool, cover?: bool, images?: list<string>, fields?: list<string>}
   */
  public static function reason(string $category, ?array $flagged_assets = null, bool $studio = false): array
  {
    $reason = ['category' => $category];
    $images = [];
    $fields = [];
    foreach ($flagged_assets ?? [] as $asset) {
      if ('text' === $asset['type']) {
        if (isset($asset['field'])) {
          $fields[] = $asset['field'];
        }
      } elseif ($studio) {
        $reason['cover'] = true;
      } elseif (1 === preg_match('~^(screen_|(manual_|automatic_)?screenshot\.)~', $asset['name'])) {
        $reason['screenshot'] = true;
      } else {
        $name = (string) preg_replace('~^[0-9a-f]{32}_~i', '', pathinfo($asset['name'], PATHINFO_FILENAME));
        if ('' !== $name) {
          $images[] = $name;
        }
      }
    }

    if ([] !== $images) {
      $reason['images'] = array_values(array_unique($images));
    }

    if ([] !== $fields) {
      $reason['fields'] = array_values(array_unique($fields));
    }

    return $reason;
  }

  #[\Override]
  public function getTwigTemplate(): string
  {
    return $this->twig_template;
  }
}
