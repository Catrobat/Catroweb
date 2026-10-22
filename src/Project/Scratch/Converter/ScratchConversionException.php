<?php

declare(strict_types=1);

namespace App\Project\Scratch\Converter;

/**
 * A Scratch project could not be fetched or converted; the message is safe to show to the user.
 * The code tells the API which status to answer with; anything else means the project can't be converted.
 */
class ScratchConversionException extends \RuntimeException
{
  final public const int NOT_FOUND = 404;
  final public const int RATE_LIMITED = 429;
  final public const int BUSY = 503;
  /** Scratch could not be reached or failed: not the project's fault, worth retrying later. */
  final public const int UPSTREAM = 502;
  final public const int INTERNAL = 500;
}
