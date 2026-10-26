<?php

declare(strict_types=1);

namespace App\Project\CodeView;

/**
 * A code view has to be made, but CodeViewExportStore::SLOTS exports are running (or this project's is): try again
 * in a moment.
 */
class CodeExportBusyException extends \RuntimeException
{
}
