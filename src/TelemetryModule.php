<?php

namespace Cencori;

use GuzzleHttp\Client;

/**
 * Telemetry module for reporting web traffic to the Cencori dashboard.
 *
 * Logs appear under the project's "Web Gateway" tab with your app's
 * real domain as the host.
 *
 * @example
 * $cencori->telemetry->reportWebRequest([
 *     'host' => 'myapp.example.com',
 *     'method' => 'GET',
 *     'path' => '/api/chat',
 *     'statusCode' => 200,
 * ]);
 */
class TelemetryModule
{
    private Cencori $client;

    public function __construct(Cencori $client)
    {
        $this->client = $client;
    }

    /**
     * Report a web request to the Cencori dashboard.
     *
     * Fire-and-forget — this method never throws and never blocks
     * your application. If the request fails, the error is silently
     * swallowed so it cannot disrupt your app's critical path.
     *
     * @param array $payload Web request details to log:
     *   - host: string (required) - The hostname of your application
     *   - method: string (required) - HTTP method (GET, POST, etc.)
     *   - path: string (required) - Request path
     *   - statusCode: int (required) - HTTP status code
     *   - requestId: ?string
     *   - queryString: ?string
     *   - message: ?string
     *   - userAgent: ?string
     *   - referer: ?string
     *   - ipAddress: ?string
     *   - countryCode: ?string
     *   - latencyMs: ?int
     *
     * @return void
     */
    public function reportWebRequest(array $payload): void
    {
        try {
            $build = $this->client->buildRequestOptions('POST', $payload);
            $url = $this->client->getBaseUrl() . '/api/v1/telemetry/web';

            $this->client->getHttpClient()->request('POST', $url, $build['options']);
        } catch (\Throwable $e) {
            // Telemetry is best-effort — never disrupt the customer's app.
        }
    }
}
