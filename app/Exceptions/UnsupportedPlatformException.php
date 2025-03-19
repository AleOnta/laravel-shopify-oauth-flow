<?php

namespace App\Exceptions;

use Exception;

class UnsupportedPlatformException extends Exception
{
    public function render()
    {
        return response()->json([
            'error' => 'Unsupported Platform',
            'message' => "Platform '" . $this->getMessage() . "' doesn't exists or isn't currently supported."
        ], 400);
    }
}
