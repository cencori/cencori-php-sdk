<?php

namespace Cencori\Errors;

class AuthenticationError extends CencoriError
{
    public function __construct(string $message = 'Invalid API key')
    {
        parent::__construct($message, 401, 'INVALID_API_KEY');
    }
}
