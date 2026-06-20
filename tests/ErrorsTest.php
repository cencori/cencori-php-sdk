<?php

namespace Cencori\Tests;

use PHPUnit\Framework\TestCase;
use Cencori\Errors\{
    AuthenticationError,
    CencoriError,
    InsufficientCreditsError,
    ProviderError,
    RateLimitError,
    SafetyError,
};

class ErrorsTest extends TestCase
{
    public function test_base_error(): void
    {
        $error = new CencoriError('Something went wrong');
        $this->assertEquals('Something went wrong', $error->getMessage());
        $this->assertNull($error->getStatusCode());
        $this->assertNull($error->getErrorCode());
    }

    public function test_base_error_with_status(): void
    {
        $error = new CencoriError('Server error', 500);
        $this->assertEquals(500, $error->getStatusCode());
    }

    public function test_authentication_error(): void
    {
        $error = new AuthenticationError();
        $this->assertEquals(401, $error->getStatusCode());
        $this->assertEquals('INVALID_API_KEY', $error->getErrorCode());
        $this->assertStringContainsString('Invalid API key', (string)$error);
    }

    public function test_rate_limit_error(): void
    {
        $error = new RateLimitError();
        $this->assertEquals(429, $error->getStatusCode());
        $this->assertEquals('RATE_LIMIT_EXCEEDED', $error->getErrorCode());
    }

    public function test_safety_error(): void
    {
        $reasons = ['harmful_content', 'pii_detected'];
        $error = new SafetyError(reasons: $reasons);
        $this->assertEquals(400, $error->getStatusCode());
        $this->assertEquals($reasons, $error->getReasons());
        $this->assertStringContainsString('harmful_content', (string)$error);
    }

    public function test_safety_empty_reasons(): void
    {
        $error = new SafetyError();
        $this->assertEquals([], $error->getReasons());
    }

    public function test_insufficient_credits_error(): void
    {
        $error = new InsufficientCreditsError();
        $this->assertEquals(402, $error->getStatusCode());
        $this->assertEquals('INSUFFICIENT_CREDITS', $error->getErrorCode());
    }

    public function test_provider_error(): void
    {
        $error = new ProviderError();
        $this->assertEquals(502, $error->getStatusCode());
        $this->assertEquals('PROVIDER_ERROR', $error->getErrorCode());
    }
}
