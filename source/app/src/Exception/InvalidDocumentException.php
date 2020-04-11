<?php

namespace App\Exception;

use Throwable;

class InvalidDocumentException extends \InvalidArgumentException
{
    /**
     * @param \SplFileInfo $file
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(\SplFileInfo $file, $message = "", $code = 0, Throwable $previous = null)
    {
        parent::__construct($file->getRelativePathname().': '.$message, $code, $previous);
    }

}
