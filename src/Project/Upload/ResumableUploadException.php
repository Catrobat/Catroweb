<?php

declare(strict_types=1);

namespace App\Project\Upload;

/**
 * A request the upload can't accept; the message is a stable reason code that also goes into the upload log.
 */
final class ResumableUploadException extends \RuntimeException
{
  public const string TOO_LARGE = 'file_too_large';
  public const string TOO_MANY_OPEN = 'too_many_open_uploads';
  public const string BAD_INDEX = 'chunk_index_out_of_range';
  public const string BAD_CHUNK_SIZE = 'chunk_size_mismatch';
  public const string BAD_CHECKSUM = 'checksum_mismatch';
  public const string INCOMPLETE = 'chunks_missing';
}
