<?php

namespace App\Exceptions;

use Exception;

class InvalidRequestException extends Exception
{
    private array $errors;

    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    public function render()
    {
        $content = [
            'error' => 'Invalid Request',
            'message' => $this->getMessage()
        ];
        if (count($this->errors) > 0) {
            $content['details'] = $this->errors;
        }
        return response()->json($content, 400);
    }
}
