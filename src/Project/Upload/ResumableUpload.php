<?php

declare(strict_types=1);

namespace App\Project\Upload;

final readonly class ResumableUpload
{
  public function __construct(
    public string $id,
    public string $user_id,
    public int $file_size,
    public int $chunk_size,
    public int $total_chunks,
    public ?string $checksum,
    public string $flavor,
    public bool $private,
    public ?string $project_id,
    public \DateTimeImmutable $expires_at,
  ) {
  }

  public function expectedChunkSize(int $index): int
  {
    return $index === $this->total_chunks - 1
      ? $this->file_size - $this->chunk_size * ($this->total_chunks - 1)
      : $this->chunk_size;
  }

  public function isExpired(): bool
  {
    return $this->expires_at <= new \DateTimeImmutable();
  }

  /**
   * @return array<string, mixed>
   */
  public function toArray(): array
  {
    return [
      'id' => $this->id,
      'user_id' => $this->user_id,
      'file_size' => $this->file_size,
      'chunk_size' => $this->chunk_size,
      'total_chunks' => $this->total_chunks,
      'checksum' => $this->checksum,
      'flavor' => $this->flavor,
      'private' => $this->private,
      'project_id' => $this->project_id,
      'expires_at' => $this->expires_at->format(\DATE_ATOM),
    ];
  }

  /**
   * @param array<string, mixed> $data
   */
  public static function fromArray(array $data): self
  {
    return new self(
      (string) $data['id'],
      (string) $data['user_id'],
      (int) $data['file_size'],
      (int) $data['chunk_size'],
      (int) $data['total_chunks'],
      isset($data['checksum']) ? (string) $data['checksum'] : null,
      (string) $data['flavor'],
      (bool) $data['private'],
      isset($data['project_id']) ? (string) $data['project_id'] : null,
      new \DateTimeImmutable((string) $data['expires_at']),
    );
  }
}
