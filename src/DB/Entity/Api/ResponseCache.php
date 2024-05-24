<?php

declare(strict_types=1);

namespace App\DB\Entity\Api;

use App\Api\Services\ResponseCache\ResponseCacheRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'response_cache')]
#[ORM\Entity(repositoryClass: ResponseCacheRepository::class)]
class ResponseCache
{
  #[ORM\Id]
  #[ORM\Column(type: Types::STRING)]
  private ?string $id = null;

  #[ORM\Column(type: Types::INTEGER)]
  private int $response_code;

  #[ORM\Column(type: Types::TEXT)]
  private string $response;

  #[ORM\Column(type: Types::STRING)]
  private string $response_headers;

  #[ORM\Column(type: Types::DATETIME_MUTABLE)]
  protected \DateTime $cached_at;

  public function setId(string $cache_id): self
  {
    $this->id = $cache_id;

    return $this;
  }

  public function getId(): ?string
  {
    return $this->id;
  }

  public function getResponseHeaders(): string
  {
    return $this->response_headers;
  }

  public function setResponseHeaders(string $response_headers): self
  {
    $this->response_headers = $response_headers;

    return $this;
  }

  public function getResponse(): string
  {
    return $this->response;
  }

  public function setResponse(string $response): self
  {
    $this->response = $response;

    return $this;
  }

  public function getResponseCode(): int
  {
    return $this->response_code;
  }

  public function setResponseCode(int $response_code): self
  {
    $this->response_code = $response_code;

    return $this;
  }

  public function getCachedAt(): \DateTime
  {
    return $this->cached_at;
  }

  #[ORM\PrePersist]
  #[ORM\PreUpdate]
  public function updateTimestamps(): self
  {
    $this->cached_at = new \DateTime('now');

    return $this;
  }
}
