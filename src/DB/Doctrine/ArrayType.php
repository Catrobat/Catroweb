<?php

declare(strict_types=1);

namespace App\DB\Doctrine;

namespace App\DB\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

class ArrayType extends Type
{
  #[\Override]
  public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
  {
    return 'JSON';
  }

  #[\Override]
  public function convertToPHPValue(mixed $value, AbstractPlatform $platform): mixed
  {
    return json_decode($value, true) ?? [];
  }

  #[\Override]
  public function convertToDatabaseValue(mixed $value, AbstractPlatform $platform): string
  {
    return json_encode($value) ?: '';
  }

  public function getName(): string
  {
    return 'array';
  }
}
