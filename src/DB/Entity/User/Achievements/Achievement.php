<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Achievements;

use App\DB\EntityRepository\User\Achievements\AchievementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'achievement')]
#[ORM\Entity(repositoryClass: AchievementRepository::class)]
class Achievement
{
  /**
   * Static Achievements - added/updated with UpdateAchievementsCommand.
   */
  final public const string BRONZE_USER = 'bronze_user';

  final public const string SILVER_USER = 'silver_user';

  final public const string GOLD_USER = 'gold_user';

  final public const string DIAMOND_USER = 'diamond_user';

  final public const string PERFECT_PROFILE = 'perfect_profile';

  final public const string CODING_JAM_09_2021 = 'coding_jam_09_2021';

  final public const string BILINGUAL = 'bilingual';

  final public const string TRILINGUAL = 'trilingual';

  final public const string LINGUIST = 'linguist';

  final public const string ACCOUNT_CREATED = 'verified_developer';

  final public const string ACCOUNT_VERIFICATION = 'account_verification';

  #[ORM\Column(name: 'id', type: Types::INTEGER)]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\Column(name: 'internal_title', type: Types::STRING, unique: true, nullable: false)]
  protected string $internal_title = '';

  #[ORM\Column(name: 'title_ltm_code', type: Types::STRING, nullable: false)]
  protected string $title_ltm_code = '';

  #[ORM\Column(name: 'internal_description', type: Types::TEXT, nullable: false)]
  protected string $internal_description = '';

  #[ORM\Column(name: 'description_ltm_code', type: Types::STRING, nullable: false)]
  protected string $description_ltm_code = '';

  #[ORM\Column(name: 'badge_svg_path', type: Types::STRING, nullable: false)]
  protected string $badge_svg_path = '';

  #[ORM\Column(name: 'badge_locked_svg_path', type: Types::STRING, nullable: false)]
  protected string $badge_locked_svg_path = '';

  #[ORM\Column(name: 'banner_svg_path', type: Types::STRING, nullable: false)]
  protected string $banner_svg_path = '';

  #[ORM\Column(name: 'banner_color', type: Types::STRING, nullable: false)]
  protected string $banner_color = '';

  #[ORM\Column(name: 'enabled', type: Types::BOOLEAN, options: ['default' => true])]
  protected bool $enabled = true;

  #[ORM\Column(name: 'priority', type: Types::INTEGER, nullable: false)]
  protected int $priority;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): Achievement
  {
    $this->id = $id;

    return $this;
  }

  public function getInternalTitle(): string
  {
    return $this->internal_title;
  }

  public function setInternalTitle(string $internal_title): Achievement
  {
    $this->internal_title = $internal_title;

    return $this;
  }

  public function getTitleLtmCode(): string
  {
    return $this->title_ltm_code;
  }

  public function setTitleLtmCode(string $title_ltm_code): Achievement
  {
    $this->title_ltm_code = $title_ltm_code;

    return $this;
  }

  public function getInternalDescription(): string
  {
    return $this->internal_description;
  }

  public function setInternalDescription(string $internal_description): Achievement
  {
    $this->internal_description = $internal_description;

    return $this;
  }

  public function getDescriptionLtmCode(): string
  {
    return $this->description_ltm_code;
  }

  public function setDescriptionLtmCode(string $description_ltm_code): Achievement
  {
    $this->description_ltm_code = $description_ltm_code;

    return $this;
  }

  public function getBadgeLockedSvgPath(): string
  {
    return $this->badge_locked_svg_path;
  }

  public function setBadgeLockedSvgPath(string $badge_locked_svg_path): Achievement
  {
    $this->badge_locked_svg_path = $badge_locked_svg_path;

    return $this;
  }

  public function getBadgeSvgPath(): string
  {
    return $this->badge_svg_path;
  }

  public function setBadgeSvgPath(string $badge_svg_path): Achievement
  {
    $this->badge_svg_path = $badge_svg_path;

    return $this;
  }

  public function getBannerSvgPath(): string
  {
    return $this->banner_svg_path;
  }

  public function setBannerSvgPath(string $banner_svg_path): Achievement
  {
    $this->banner_svg_path = $banner_svg_path;

    return $this;
  }

  public function getBannerColor(): string
  {
    return $this->banner_color;
  }

  public function setBannerColor(string $banner_color): Achievement
  {
    $this->banner_color = $banner_color;

    return $this;
  }

  public function isEnabled(): bool
  {
    return $this->enabled;
  }

  public function setEnabled(bool $enabled): Achievement
  {
    $this->enabled = $enabled;

    return $this;
  }

  public function getPriority(): int
  {
    return $this->priority;
  }

  public function setPriority(int $priority): Achievement
  {
    $this->priority = $priority;

    return $this;
  }
}
