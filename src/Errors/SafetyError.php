<?php

namespace Cencori\Errors;

class SafetyError extends CencoriError
{
    private array $reasons;

    public function __construct(
        string $message = 'Content safety violation',
        array $reasons = []
    ) {
        parent::__construct($message, 400, 'SAFETY_VIOLATION');
        $this->reasons = $reasons;
    }

    public function getReasons(): array
    {
        return $this->reasons;
    }

    public function __toString(): string
    {
        $base = parent::__toString();
        if (!empty($this->reasons)) {
            $base .= ' Reasons: ' . implode(', ', $this->reasons);
        }
        return $base;
    }
}
