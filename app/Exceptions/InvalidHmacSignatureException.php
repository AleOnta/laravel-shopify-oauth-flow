<?php

namespace App\Exceptions;

use Exception;

class InvalidHmacSignatureException extends Exception
{
    public function render()
    {
        return response()->json([
            'error' => 'Invalid HMac Signature',
            'message' => $this->getMessage()
        ], 400);
    }
}
