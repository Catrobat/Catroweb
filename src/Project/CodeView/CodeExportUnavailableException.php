<?php

declare(strict_types=1);

namespace App\Project\CodeView;

/**
 * A code view cannot be made right now, which may change: CodeViewExportStore::SLOTS exports are running (or this
 * project's is), Node is missing, crashed or timed out, or no export is deployed. Nothing is stored; try again.
 */
class CodeExportUnavailableException extends \RuntimeException
{
}
