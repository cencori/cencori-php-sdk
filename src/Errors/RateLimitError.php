<?php

namespace Cencori\Errors;

class RateLimitError extends CencoriError
{
    public function __construct(string $message = 'Rate limit exceeded')
    {
        parent::__construct($message, 429, 'RATE_LIMIT_EXCEEDED');
    }
}
