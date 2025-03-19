<?php

namespace App\Exceptions;

use Exception;

class OAuthException extends Exception
{
    private array $errors;

    public function __construct(string $message, array $errors = [])
    {
        parent::__construct($message);
        $this->errors = $errors;
    }

    private function getErrors()
    {
        return $this->errors;
    }

    public function render()
    {
        $content = [
            'error' => 'OAuth Error',
            'message' => $this->getMessage(),
        ];
        if (count($this->errors) > 0) {
            $content['details'] = $this->getErrors();
        }
        return response()->json($content, 400);
    }
}
