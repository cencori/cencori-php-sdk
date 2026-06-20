<?php

namespace Cencori\Errors;

class CencoriError extends \RuntimeException
{
    private ?int $statusCode;
    private ?string $errorCode;

    public function __construct(
        string $message = 'An error occurred',
        ?int $statusCode = null,
        ?string $errorCode = null,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, 0, $previous);
        $this->statusCode = $statusCode;
        $this->errorCode = $errorCode;
    }

    public function getStatusCode(): ?int
    {
        return $this->statusCode;
    }

    public function getErrorCode(): ?string
    {
        return $this->errorCode;
    }

    public function __toString(): string
    {
        $parts = [$this->message];
        if ($this->errorCode !== null && $this->statusCode !== null) {
            $parts[] = "(code: {$this->errorCode}, status: {$this->statusCode})";
        } elseif ($this->statusCode !== null) {
            $parts[] = "(status: {$this->statusCode})";
        }
        return implode(' ', $parts);
    }
}
