<?php

declare(strict_types=1);

namespace App\DB\Entity\User\Achievements;

use App\DB\Entity\User\User;
use App\DB\EntityRepository\User\Achievements\UserAchievementRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'user_achievement')]
#[ORM\UniqueConstraint(name: 'user_achievement_unique', columns: ['user', 'achievement'])]
#[ORM\Entity(repositoryClass: UserAchievementRepository::class)]
class UserAchievement
{
  #[ORM\Column(name: 'id', type: 'integer')]
  #[ORM\Id]
  #[ORM\GeneratedValue(strategy: 'AUTO')]
  protected ?int $id = null;

  #[ORM\JoinColumn(name: 'user', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: User::class)]
  protected User $user;

  #[ORM\JoinColumn(name: 'achievement', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
  #[ORM\ManyToOne(targetEntity: Achievement::class)]
  protected Achievement $achievement;

  #[ORM\Column(name: 'unlocked_at', type: 'datetime', nullable: true)]
  protected ?\DateTimeInterface $unlocked_at = null;

  #[ORM\Column(name: 'seen_at', type: 'datetime', nullable: true)]
  protected ?\DateTimeInterface $seen_at = null;

  public function getId(): ?int
  {
    return $this->id;
  }

  public function setId(int $id): UserAchievement
  {
    $this->id = $id;

    return $this;
  }

  public function getUser(): User
  {
    return $this->user;
  }

  public function setUser(User $user): UserAchievement
  {
    $this->user = $user;

    return $this;
  }

  public function getAchievement(): Achievement
  {
    return $this->achievement;
  }

  public function setAchievement(Achievement $achievement): UserAchievement
  {
    $this->achievement = $achievement;

    return $this;
  }

  public function getUnlockedAt(): ?\DateTimeInterface
  {
    return $this->unlocked_at;
  }

  public function setUnlockedAt(?\DateTimeInterface $unlocked_at): UserAchievement
  {
    $this->unlocked_at = $unlocked_at;

    return $this;
  }

  public function getSeenAt(): ?\DateTimeInterface
  {
    return $this->seen_at;
  }

  public function setSeenAt(?\DateTime $seen_at): UserAchievement
  {
    $this->seen_at = $seen_at;

    return $this;
  }
}
