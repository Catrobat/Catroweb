<?php

namespace App\DB\Entity\System;

use App\DB\EntityRepository\System\StatisticRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StatisticRepository::class)]
class Statistic
{
  #[ORM\Id]
  #[ORM\GeneratedValue]
  #[ORM\Column]
  private ?int $id = null;

  #[ORM\Column(type: Types::BIGINT)]
  private ?string $projects = null;

  #[ORM\Column(type: Types::BIGINT)]
  private ?string $users = null;

  public function setId(int $id): static
  {
    $this->id = $id;

    return $this;
  }

  public function getId(): ?int
  {
    return $this->id;
  }

  public function getProjects(): ?string
  {
    return $this->projects;
  }

  public function setProjects(string $projects): static
  {
    $this->projects = $projects;

    return $this;
  }

  public function getUsers(): ?string
  {
    return $this->users;
  }

  public function setUsers(string $users): static
  {
    $this->users = $users;

    return $this;
  }
}
