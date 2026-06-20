<?php

namespace Cencori\Types;

/**
 * Payload for web request telemetry reporting.
 *
 * @see \Cencori\TelemetryModule::reportWebRequest()
 */
class WebTelemetryPayload
{
    public function __construct(
        public readonly string $host,
        public readonly string $method,
        public readonly string $path,
        public readonly int $statusCode,
        public readonly ?string $requestId = null,
        public readonly ?string $queryString = null,
        public readonly ?string $message = null,
        public readonly ?string $userAgent = null,
        public readonly ?string $referer = null,
        public readonly ?string $ipAddress = null,
        public readonly ?string $countryCode = null,
        public readonly ?int $latencyMs = null,
    ) {}

    public function toArray(): array
    {
        $result = [
            'host' => $this->host,
            'method' => $this->method,
            'path' => $this->path,
            'statusCode' => $this->statusCode,
        ];

        if ($this->requestId !== null) {
            $result['requestId'] = $this->requestId;
        }
        if ($this->queryString !== null) {
            $result['queryString'] = $this->queryString;
        }
        if ($this->message !== null) {
            $result['message'] = $this->message;
        }
        if ($this->userAgent !== null) {
            $result['userAgent'] = $this->userAgent;
        }
        if ($this->referer !== null) {
            $result['referer'] = $this->referer;
        }
        if ($this->ipAddress !== null) {
            $result['ipAddress'] = $this->ipAddress;
        }
        if ($this->countryCode !== null) {
            $result['countryCode'] = $this->countryCode;
        }
        if ($this->latencyMs !== null) {
            $result['latencyMs'] = $this->latencyMs;
        }

        return $result;
    }
}
