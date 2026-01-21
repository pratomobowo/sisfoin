<?php

namespace App\Exceptions;

use Exception;

class AttendanceProcessingException extends Exception
{
    protected $context = [];

    public function __construct($message = '', $code = 0, Exception $previous = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->context = $context;
    }

    public function getContext()
    {
        return $this->context;
    }

    public function render()
    {
        return response()->json([
            'error' => $this->getMessage(),
            'context' => $this->context,
            'code' => $this->getCode()
        ], 500);
    }
}
