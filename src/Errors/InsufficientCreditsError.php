<?php

namespace Cencori\Errors;

class InsufficientCreditsError extends CencoriError
{
    public function __construct(string $message = 'Insufficient credits')
    {
        parent::__construct($message, 402, 'INSUFFICIENT_CREDITS');
    }
}
