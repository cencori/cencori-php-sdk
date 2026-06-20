<?php

namespace Cencori\Errors;

class ProviderError extends CencoriError
{
    public function __construct(string $message = 'Provider error')
    {
        parent::__construct($message, 502, 'PROVIDER_ERROR');
    }
}
